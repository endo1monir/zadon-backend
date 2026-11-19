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
use App\Support\CartManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResponseTrait;

    public function show(Request $request): JsonResponse
    {
        return $this->cartResponse($request, $this->activeCart($request->user()->id));
    }

    public function count(Request $request): JsonResponse
    {
        $cart = $this->activeCart($request->user()->id);

        return $this->successReturn([
            'items_count' => $cart ? (int) $cart->items()->sum('quantity') : 0,
        ]);
    }

    public function addItem(AddCartItemRequest $request, CartManager $cartManager): JsonResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($request->integer('product_id'));

        if ($product->store_id !== $request->integer('store_id')) {
            return $this->failReturn('messages.product_not_in_store');
        }

        if ($product->stock < $request->integer('quantity')) {
            return $this->failReturn('messages.not_enough_stock');
        }

        $cart = $cartManager->addItem($user, $product, $request->integer('quantity'), $request->validated('options'));

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

    /**
     * Delete the customer's active cart. Its items are removed with it.
     */
    public function clear(Request $request): JsonResponse
    {
        Cart::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->delete();

        return $this->cartResponse($request, null);
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
