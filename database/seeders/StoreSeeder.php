<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $pharmacyCategory = Category::where('slug', 'pharmacies')->firstOrFail();
        $supermarketCategory = Category::where('slug', 'supermarkets')->firstOrFail();

        $cities = [
            'الرياض' => City::where('name_ar', 'الرياض')->firstOrFail(),
            'جدة' => City::where('name_ar', 'جدة')->firstOrFail(),
        ];

        $stores = [
            [
                'owner' => 'ahmed@zadon.sa',
                'category' => $pharmacyCategory,
                'name_ar' => 'صيدلية زادون',
                'name_en' => 'Zadon Pharmacy',
                'city' => 'الرياض',
                'status' => 'open',
                'is_verified' => true,
                'is_open_24_7' => true,
                'delivery_fee' => 0,
                'prep_time_min' => 20,
                'rating' => 4.8,
                'rating_count' => 214,
            ],
            [
                'owner' => 'sara@zadon.sa',
                'category' => $pharmacyCategory,
                'name_ar' => 'صيدلية النخيل',
                'name_en' => 'Al Nakheel Pharmacy',
                'city' => 'جدة',
                'status' => 'open',
                'is_verified' => true,
                'delivery_fee' => 8,
                'prep_time_min' => 25,
                'rating' => 4.6,
                'rating_count' => 96,
            ],
            [
                'owner' => 'khaled@zadon.sa',
                'category' => $supermarketCategory,
                'name_ar' => 'سوبر ماركت السوق',
                'name_en' => 'Al Souq Supermarket',
                'city' => 'الرياض',
                'status' => 'busy',
                'is_verified' => true,
                'delivery_fee' => 15,
                'min_order' => 20,
                'prep_time_min' => 30,
                'rating' => 4.2,
                'rating_count' => 58,
            ],
        ];

        foreach ($stores as $store) {
            /** @var User $owner */
            $owner = User::where('email', $store['owner'])->firstOrFail();

            Store::create([
                'owner_id' => $owner->id,
                'category_id' => $store['category']->id,
                'city_id' => $cities[$store['city']]->id,
                'name_ar' => $store['name_ar'],
                'name_en' => $store['name_en'],
                'address' => "فرع {$store['city']} - طريق الملك فهد",
                'phone' => $owner->phone,
                'email' => $owner->email,
                'cr_number' => 'CR-'.$owner->id.'00001',
                'vat_number' => '300123456700003',
                'status' => $store['status'],
                'prep_time_min' => $store['prep_time_min'],
                'delivery_fee' => $store['delivery_fee'],
                'min_order' => $store['min_order'] ?? 0,
                'manager_name' => $owner->name,
                'is_verified' => $store['is_verified'],
                'is_open_24_7' => $store['is_open_24_7'] ?? false,
                'opening_time' => '08:00',
                'closing_time' => '02:00',
                'delivery_radius_km' => 15,
                'rating' => $store['rating'],
                'rating_count' => $store['rating_count'],
                'is_active' => true,
            ]);
        }
    }
}
