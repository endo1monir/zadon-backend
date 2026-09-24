<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            'https://placehold.co/1200x400/2563eb/ffffff?text=Zadon+Offers',
            'https://placehold.co/1200x400/dc2626/ffffff?text=Free+Delivery',
            'https://placehold.co/1200x400/059669/ffffff?text=New+Arrivals',
        ];

        foreach ($banners as $image) {
            Banner::create(['image' => $image]);
        }
    }
}
