<?php

namespace App\Support;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Turns a customer's active cart into an order. Shared by the checkout
 * endpoint and the chatbot so both apply the same stock, wallet and total rules.
 */
class OrderCheckout
{
    public const VAT_RATE = 0.15;

    public function activeCart(User $user): ?Cart
    {
        return Cart::with('items.product', 'store.city')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Why the cart cannot be checked out, as a translated message, or null when it can.
     */
    public function failureReason(User $user, ?Cart $cart, string $paymentMethod): ?string
    {
        if (! $cart || $cart->items->isEmpty()) {
            return trans('messages.cart_empty');
        }

        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return trans('messages.stock_left', [
                    'stock' => $item->product->stock,
                    'name' => $item->product->name_ar,
                ]);
            }
        }

        if ($paymentMethod === 'wallet') {
            $wallet = $user->wallet()->firstOrCreate([]);

            if ((float) $wallet->balance < $this->totals($cart)['total']) {
                return trans('messages.insufficient_wallet_balance');
            }
        }

        return null;
    }

    /**
     * @return array{subtotal: float, vat: float, delivery_fee: float, total: float}
     */
    public function totals(Cart $cart): array
    {
        $subtotal = round($cart->items->sum(fn ($item) => $item->unit_price * $item->quantity), 2);
        $vat = round($subtotal * self::VAT_RATE, 2);
        $deliveryFee = (float) $cart->store->delivery_fee;

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'delivery_fee' => $deliveryFee,
            'total' => round($subtotal + $vat + $deliveryFee, 2),
        ];
    }

    /**
     * Create the order, take the stock, charge the wallet when used and close the cart.
     * Call failureReason() first.
     */
    public function place(User $user, Cart $cart, string $paymentMethod, ?Address $address, ?string $notes = null): Order
    {
        $totals = $this->totals($cart);

        return DB::transaction(function () use ($user, $cart, $paymentMethod, $address, $notes, $totals): Order {
            $paymentStatus = $this->chargeWallet($user, $paymentMethod, $totals['total']);

            $order = Order::create([
                'order_number' => $this->nextOrderNumber(),
                'user_id' => $user->id,
                'store_id' => $cart->store_id,
                'status' => 'new',
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat'],
                'delivery_fee' => $totals['delivery_fee'],
                'total' => $totals['total'],
                'delivery_address_id' => $address->id ?? null,
                'delivery_address' => $address?->full_address,
                'city' => $cart->store?->city?->name_ar,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'notes' => $notes,
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
    }

    private function chargeWallet(User $user, string $method, float $total): string
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
