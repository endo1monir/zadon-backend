<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            ['key' => 'card', 'name_ar' => 'بطاقة مدى أو ائتمانية', 'name_en' => 'Mada or credit card', 'sort_order' => 1],
            ['key' => 'apple_pay', 'name_ar' => 'Apple Pay', 'name_en' => 'Apple Pay', 'sort_order' => 2],
            ['key' => 'wallet', 'name_ar' => 'محفظة زادون', 'name_en' => 'Zadon wallet', 'sort_order' => 3],
            ['key' => 'cash', 'name_ar' => 'الدفع عند الاستلام', 'name_en' => 'Cash on delivery', 'sort_order' => 4],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::create($paymentMethod);
        }
    }
}
