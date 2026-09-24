<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $carts = [
            [
                'status' => 'active',
                'user' => 'abdullah@zadon.sa',
                'store' => 'صيدلية زادون',
                'items' => [
                    ['sku' => 'PAN-01', 'quantity' => 2],
                    ['sku' => 'VIT-M', 'quantity' => 1],
                ],
            ],
            [
                'status' => 'active',
                'user' => 'fatima@zadon.sa',
                'store' => 'سوبر ماركت السوق',
                'items' => [
                    ['sku' => 'RICE-5', 'quantity' => 1],
                    ['sku' => 'MILK-1', 'quantity' => 2],
                ],
            ],
            [
                'status' => 'abandoned',
                'user' => 'demo@zadon.sa',
                'store' => 'صيدلية النخيل',
                'items' => [
                    ['sku' => 'VIT-B12', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($carts as $cart) {
            $cartModel = Cart::create([
                'user_id' => User::where('email', $cart['user'])->firstOrFail()->id,
                'store_id' => Store::where('name_ar', $cart['store'])->firstOrFail()->id,
                'status' => $cart['status'],
            ]);

            foreach ($cart['items'] as $item) {
                $product = Product::where('sku', $item['sku'])->firstOrFail();

                CartItem::create([
                    'cart_id' => $cartModel->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'options' => $product->options,
                ]);
            }
        }
    }
}
