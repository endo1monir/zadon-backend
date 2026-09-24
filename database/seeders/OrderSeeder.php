<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private const VAT_RATE = 0.15;

    public function run(): void
    {
        $orders = [
            [
                'number' => 'ZDN-2026-0001',
                'user' => 'abdullah@zadon.sa',
                'store' => 'صيدلية زادون',
                'status' => 'delivered',
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'delivery_fee' => 0,
                'items' => [
                    ['sku' => 'PAN-01', 'quantity' => 2],
                    ['sku' => 'VIT-C', 'quantity' => 1],
                ],
                'age_days' => 3,
            ],
            [
                'number' => 'ZDN-2026-0002',
                'user' => 'fatima@zadon.sa',
                'store' => 'صيدلية النخيل',
                'status' => 'preparing',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'delivery_fee' => 8,
                'items' => [
                    ['sku' => 'VIT-B12', 'quantity' => 1],
                    ['sku' => 'SKIN-CRM', 'quantity' => 1],
                ],
                'age_days' => 0,
            ],
            [
                'number' => 'ZDN-2026-0003',
                'user' => 'mohammed@zadon.sa',
                'store' => 'سوبر ماركت السوق',
                'status' => 'new',
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'delivery_fee' => 15,
                'items' => [
                    ['sku' => 'WTR-15', 'quantity' => 3],
                    ['sku' => 'RICE-5', 'quantity' => 1],
                    ['sku' => 'CHP-1', 'quantity' => 2],
                ],
                'age_days' => 0,
            ],
            [
                'number' => 'ZDN-2026-0004',
                'user' => 'noura@zadon.sa',
                'store' => 'صيدلية النخيل',
                'status' => 'out_for_delivery',
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'delivery_fee' => 8,
                'items' => [
                    ['sku' => 'COLD-PK', 'quantity' => 1],
                    ['sku' => 'THERMO', 'quantity' => 1],
                ],
                'age_days' => 1,
            ],
        ];

        foreach ($orders as $order) {
            $user = User::where('email', $order['user'])->firstOrFail();
            $store = Store::where('name_ar', $order['store'])->firstOrFail();
            $address = Address::where('user_id', $user->id)->where('is_default', true)->first();

            $orderItems = [];
            $subtotal = 0;

            foreach ($order['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->firstOrFail();
                $lineTotal = round($product->price * $item['quantity'], 2);
                $subtotal = round($subtotal + $lineTotal, 2);

                $orderItems[] = [
                    'product' => $product,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                ];
            }

            $vatAmount = round($subtotal * self::VAT_RATE, 2);
            $deliveryFee = $order['delivery_fee'];
            $total = round($subtotal + $vatAmount + $deliveryFee, 2);

            $createdAt = now()->subDays($order['age_days']);
            $statusTimestamps = match ($order['status']) {
                'delivered' => [
                    'accepted_at' => $createdAt->addHours(1),
                    'prepared_at' => $createdAt->addHours(2),
                    'ready_at' => $createdAt->addHours(3),
                    'out_for_delivery_at' => $createdAt->addHours(4),
                    'delivered_at' => $createdAt->addHours(6),
                ],
                'preparing' => ['accepted_at' => $createdAt->addHours(1)],
                'out_for_delivery' => [
                    'accepted_at' => $createdAt->addHours(1),
                    'prepared_at' => $createdAt->addHours(2),
                    'ready_at' => $createdAt->addHours(3),
                    'out_for_delivery_at' => $createdAt->addHours(4),
                ],
                default => [],
            };

            $orderModel = Order::create([
                'order_number' => $order['number'],
                'user_id' => $user->id,
                'store_id' => $store->id,
                'status' => $order['status'],
                'payment_method' => $order['payment_method'],
                'payment_status' => $order['payment_status'],
                'subtotal' => $subtotal,
                'vat_amount' => $vatAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'delivery_address_id' => $address?->id,
                'delivery_address' => $address?->full_address,
                'city' => $address?->city,
                'customer_name' => $user->name,
                'customer_phone' => $user->phone,
                'notes' => null,
                ...$statusTimestamps,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $orderModel->id,
                    'product_id' => $item['product']->id,
                    'product_name_ar' => $item['product']->name_ar,
                    'product_name_en' => $item['product']->name_en,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['product']->unit_ar,
                    'image' => $item['product']->image,
                    'options' => $item['product']->options,
                    'packed' => $order['status'] === 'delivered',
                ]);
            }
        }
    }
}
