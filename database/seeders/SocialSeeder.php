<?php

namespace Database\Seeders;

use App\Models\Social;
use Illuminate\Database\Seeder;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $socials = [
            ['name_ar' => 'X (تويتر)', 'name_en' => 'X (Twitter)', 'link' => 'https://x.com/zadon_sa'],
            ['name_ar' => 'انستقرام', 'name_en' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa'],
            ['name_ar' => 'سناب شات', 'name_en' => 'Snapchat', 'link' => 'https://snapchat.com/add/zadon_sa'],
            ['name_ar' => 'واتساب', 'name_en' => 'WhatsApp', 'link' => 'https://wa.me/966500000000'],
            ['name_ar' => 'تيك توك', 'name_en' => 'TikTok', 'link' => 'https://tiktok.com/@zadon_sa'],
        ];

        foreach ($socials as $social) {
            Social::create($social);
        }
    }
}
