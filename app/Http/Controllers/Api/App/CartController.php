<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\AddCartItemRequest;
use App\Http\Requests\Api\App\UpdateCartItemRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResponseTrait;

    public function show(Request $request): JsonResponse
    {
        return $this->cartResponse($request, $this->activeCart($request->user()->id));
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($request->integer('product_id'));

        if ($product->store_id !== $request->integer('store_id')) {
            return $this->failReturn('messages.product_not_in_store');
        }

        if ($product->stock < $request->integer('quantity')) {
            return $this->failReturn('messages.not_enough_stock');
        }

        $cart = $this->activeCart($user->id);

        if ($cart && $cart->store_id !== $product->store_id) {
            $cart->update(['status' => 'abandoned']);
            $cart = null;
        }

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'store_id' => $product->store_id,
                'status' => 'active',
            ]);
        }

        $item = $cart->items()->where('product_id', $product->id)->first();

        if ($item) {
            $item->increment('quantity', $request->integer('quantity'));
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->integer('quantity'),
                'unit_price' => $product->price,
                'options' => $request->validated('options'),
            ]);
        }

        return $this->cartResponse($request, $cart);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $cart = $cartItem->cart;

        abort_unless($cart->user_id === $request->user()->id && $cart->status === 'active', 404);

        if ($cartItem->product->stock < $request->integer('quantity')) {
            return $this->failReturn('messages.not_enough_stock');
        }

        $cartItem->update($request->validated());

        return $this->cartResponse($request, $cart);
    }

    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $cart = $cartItem->cart;

        abort_unless($cart->user_id === $request->user()->id && $cart->status === 'active', 404);

        $cartItem->delete();

        return $this->cartResponse($request, $cart);
    }

    private function cartResponse(Request $request, ?Cart $cart): JsonResponse
    {
        $address = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->first();

        $paymentMethods = PaymentMethod::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->successReturn([
            'cart' => new CartResource($cart?->load('items.product', 'store')),
            'payment_methods' => PaymentMethodResource::collection($paymentMethods),
            'default_address' => $address ? new AddressResource($address) : null,
        ]);
    }

    private function activeCart(int $userId): ?Cart
    {
        return Cart::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }
}
