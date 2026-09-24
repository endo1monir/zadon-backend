<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['الرياض', 'Riyadh'],
            ['جدة', 'Jeddah'],
            ['مكة المكرمة', 'Mecca'],
            ['المدينة المنورة', 'Medina'],
            ['الدمام', 'Dammam'],
            ['الخبر', 'Khobar'],
            ['الظهران', 'Dhahran'],
            ['الطائف', 'Taif'],
            ['تبوك', 'Tabuk'],
            ['بريدة', 'Buraidah'],
            ['خميس مشيط', 'Khamis Mushait'],
            ['القطيف', 'Qatif'],
            ['الجبيل', 'Jubail'],
            ['الأحساء', 'Al-Ahsa'],
            ['أبها', 'Abha'],
            ['ينبع', 'Yanbu'],
            ['حائل', 'Hail'],
            ['نجران', 'Najran'],
            ['جازان', 'Jazan'],
            ['عرعر', 'Arar'],
            ['سكاكا', 'Sakaka'],
            ['بيشة', 'Bisha'],
            ['الخفجي', 'Khafji'],
            ['عيون الجواء', 'Uyun AlJiwa'],
            ['الزلفي', 'Az Zulfi'],
        ];

        foreach ($cities as $index => [$nameAr, $nameEn]) {
            City::create([
                'name_ar' => $nameAr,
                'name_en' => $nameEn,
                'is_active' => true,
                'sort_order' => $index,
            ]);
        }
    }
}
