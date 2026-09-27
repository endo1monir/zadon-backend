<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    use ResponseTrait;

    public function store(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();

        $cart = Cart::with('items.product', 'store')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return $this->failReturn('messages.cart_empty');
        }

        $address = $request->filled('address_id')
            ? $user->addresses()->findOrFail($request->integer('address_id'))
            : $user->addresses()->where('is_default', true)->first();

        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return $this->failReturn(trans('messages.stock_left', [
                    'stock' => $item->product->stock,
                    'name' => $item->product->name_ar,
                ]));
            }
        }

        $subtotal = round($cart->items->sum(fn ($item) => $item->unit_price * $item->quantity), 2);
        $vat = round($subtotal * 0.15, 2);
        $deliveryFee = (float) $cart->store->delivery_fee;
        $total = round($subtotal + $vat + $deliveryFee, 2);

        if ($request->payment_method === 'wallet') {
            $wallet = $user->wallet()->firstOrCreate([]);

            if ((float) $wallet->balance < $total) {
                return $this->failReturn('messages.insufficient_wallet_balance');
            }
        }

        $order = DB::transaction(function () use ($request, $user, $cart, $address, $subtotal, $vat, $deliveryFee, $total): Order {
            $paymentStatus = $this->chargeWallet($user, $request->payment_method, $total);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $user->id,
                'store_id' => $cart->store_id,
                'status' => 'new',
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'vat_amount' => $vat,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'delivery_address_id' => $address->id ?? null,
                'delivery_address' => $address?->full_address,
                'city' => $address?->city ?? $cart->store->city,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'notes' => $request->notes,
            ]);

            foreach ($cart->items as $item) {
                $product = $item->product;
                $product->decrement('stock', $item->quantity);
                $product->increment('sales_count', $item->quantity);

                $product->stockAdjustments()->create([
                    'store_id' => $cart->store_id,
                    'type' => 'sale',
                    'quantity' => -$item->quantity,
                    'previous_stock' => $product->stock + $item->quantity,
                    'new_stock' => $product->stock,
                    'reason' => "Order {$order->order_number}",
                ]);

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name_ar' => $product->name_ar,
                    'product_name_en' => $product->name_en,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'unit' => $product->unit_ar,
                    'image' => $product->image,
                    'options' => $item->options,
                ]);
            }

            $cart->update(['status' => 'checked_out']);

            $cart->store->notifications()->create([
                'title_ar' => "طلب جديد {$order->order_number}",
                'title_en' => "New order {$order->order_number}",
                'type' => 'order',
                'order_id' => $order->id,
                'action_label_ar' => 'عرض الطلب',
                'action_label_en' => 'View order',
            ]);

            return $order;
        });

        return $this->successReturn([
            'order' => new OrderResource($order->load('items', 'store', 'paymentMethod', 'review')),
        ], code: 201);
    }

    private function chargeWallet($user, string $method, float $total): string
    {
        if ($method !== 'wallet') {
            return 'pending';
        }

        $wallet = $user->wallet()->firstOrCreate([]);

        $wallet->balance = (float) $wallet->balance - $total;
        $wallet->save();

        $wallet->transactions()->create([
            'user_id' => $user->id,
            'type' => 'debit',
            'amount' => $total,
            'balance_after' => (float) $wallet->balance,
            'description' => trans('messages.order_payment'),
        ]);

        return 'paid';
    }

    private function nextOrderNumber(): string
    {
        do {
            $number = 'ZD-'.now()->format('ymd').'-'.random_int(1000, 9999);
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
