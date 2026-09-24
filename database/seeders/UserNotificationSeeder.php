<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class UserNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            [
                'user' => 'abdullah@zadon.sa',
                'order' => 'ZDN-2026-0001',
                'type' => 'order',
                'title_ar' => 'تم توصيل طلبك',
                'title_en' => 'Your order has been delivered',
                'message_ar' => 'ألف مبروك! تم توصيل طلبك رقم ZDN-2026-0001 بنجاح.',
                'action_label_ar' => 'عرض الطلب',
                'is_read' => false,
            ],
            [
                'user' => 'ahmed@zadon.sa',
                'store' => 'صيدلية زادون',
                'order' => 'ZDN-2026-0001',
                'type' => 'order',
                'title_ar' => 'طلب جديد',
                'title_en' => 'New order received',
                'message_ar' => 'لديك طلب جديد من عبدالله الغامدي بقيمة 60.95 ر.س.',
                'action_label_ar' => 'عرض الطلب',
                'is_read' => false,
            ],
            [
                'user' => 'noura@zadon.sa',
                'order' => 'ZDN-2026-0004',
                'type' => 'order',
                'title_ar' => 'طلبك في الطريق',
                'title_en' => 'Your order is out for delivery',
                'message_ar' => 'مندوب التوصيل في الطريق الآن، طلبك سيصل خلال 25 دقيقة.',
                'action_label_ar' => 'تتبع الطلب',
                'is_read' => false,
            ],
            [
                'user' => 'sara@zadon.sa',
                'store' => 'صيدلية النخيل',
                'product' => 'THERMO',
                'type' => 'inventory',
                'title_ar' => 'تنبيه: مخزون منخفض',
                'title_en' => 'Low stock alert',
                'message_ar' => 'وصل مخزون ميزان حرارة رقمي إلى 25 وحدة وهو أقل من حد التنبيه (30).',
                'action_label_ar' => 'إدارة المخزون',
                'is_read' => false,
            ],
            [
                'user' => 'demo@zadon.sa',
                'type' => 'system',
                'title_ar' => 'مرحبًا بك في زادون',
                'title_en' => 'Welcome to Zadon',
                'message_ar' => 'تم إنشاء حسابك بنجاح. يمكنك الآن تصفح المتاجر والطلب فورًا.',
                'is_read' => false,
            ],
        ];

        foreach ($notifications as $notification) {
            $user = User::where('email', $notification['user'])->firstOrFail();
            $store = isset($notification['store'])
                ? Store::where('name_ar', $notification['store'])->first()
                : null;
            $order = isset($notification['order'])
                ? Order::where('order_number', $notification['order'])->first()
                : null;
            $product = isset($notification['product'])
                ? Product::where('sku', $notification['product'])->first()
                : null;
            unset($notification['user'], $notification['store'], $notification['order'], $notification['product']);

            UserNotification::create(array_merge($notification, [
                'user_id' => $user->id,
                'store_id' => $store?->id,
                'order_id' => $order?->id,
                'product_id' => $product?->id,
            ]));
        }
    }
}
