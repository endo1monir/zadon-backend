<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'store' => 'صيدلية زادون',
                'user' => 'abdullah@zadon.sa',
                'order' => 'ZDN-2026-0001',
                'rating' => 5,
                'courier_rating' => 5,
                'comment' => 'توصيل سريع والمنتجات ممتازة، أنصح الجميع بالطلب منهم.',
                'tags' => ['جودة ممتازة', 'التوصيل سريع'],
                'store_reply' => 'شكرًا لثقتك بنا، سعداء بخدمتك دائمًا.',
                'store_reply_date' => now()->subDays(2),
                'published' => true,
            ],
            [
                'store' => 'سوبر ماركت السوق',
                'user' => 'mohammed@zadon.sa',
                'order' => null,
                'rating' => 4,
                'courier_rating' => 4,
                'comment' => 'سوق جيد والأسعار مناسبة، فقط تأخر التوصيل قليلًا.',
                'tags' => ['أسعار مناسبة', 'توسيع'],
                'store_reply' => null,
                'store_reply_date' => null,
                'published' => true,
            ],
            [
                'store' => 'صيدلية النخيل',
                'user' => 'fatima@zadon.sa',
                'order' => null,
                'rating' => 5,
                'courier_rating' => null,
                'comment' => 'خدمة رائعة ومنتجات أصلية 100%.',
                'tags' => ['منتجات أصلية'],
                'store_reply' => 'شكرًا جزيلًا، نقدر تقييمك.',
                'store_reply_date' => now()->subDay(),
                'published' => true,
            ],
        ];

        foreach ($reviews as $review) {
            $store = Store::where('name_ar', $review['store'])->firstOrFail();
            $user = User::where('email', $review['user'])->firstOrFail();
            $order = $review['order'] !== null
                ? Order::where('order_number', $review['order'])->firstOrFail()
                : null;
            unset($review['store'], $review['user'], $review['order']);

            Review::create(array_merge($review, [
                'store_id' => $store->id,
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'customer_name' => $user->name,
                'customer_avatar' => null,
            ]));
        }
    }
}
