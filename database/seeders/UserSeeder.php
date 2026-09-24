<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'أحمد المطيري', 'email' => 'ahmed@zadon.sa', 'phone' => '0501111111', 'role' => 'vendor', 'city' => 'الرياض'],
            ['name' => 'سارة العتيبي', 'email' => 'sara@zadon.sa', 'phone' => '0502222222', 'role' => 'vendor', 'city' => 'جدة'],
            ['name' => 'خالد الحربي', 'email' => 'khaled@zadon.sa', 'phone' => '0503333333', 'role' => 'vendor', 'city' => 'الرياض'],
            ['name' => 'عبدالله الغامدي', 'email' => 'abdullah@zadon.sa', 'phone' => '0551111111', 'role' => 'customer', 'city' => 'الرياض'],
            ['name' => 'فاطمة القحطاني', 'email' => 'fatima@zadon.sa', 'phone' => '0552222222', 'role' => 'customer', 'city' => 'جدة'],
            ['name' => 'محمد الشمري', 'email' => 'mohammed@zadon.sa', 'phone' => '0553333333', 'role' => 'customer', 'city' => 'الرياض'],
            ['name' => 'نورة الدوسري', 'email' => 'noura@zadon.sa', 'phone' => '0554444444', 'role' => 'customer', 'city' => 'الرياض'],
            ['name' => 'يوسف العنزي', 'email' => 'yousef@zadon.sa', 'phone' => '0555555555', 'role' => 'customer', 'city' => 'الدمام'],
            ['name' => 'عميل تجريبي', 'email' => 'demo@zadon.sa', 'phone' => '0556666666', 'role' => 'customer', 'city' => 'الرياض'],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'role' => $user['role'],
                'city_id' => City::where('name_ar', $user['city'])->value('id'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
        }
    }
}
