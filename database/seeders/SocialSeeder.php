<?php

namespace Database\Seeders;

use App\Models\Social;
use Illuminate\Database\Seeder;

class SocialSeeder extends Seeder
{
    public function run(): void
    {
        $socials = [
            ['name' => 'X (Twitter)', 'link' => 'https://x.com/zadon_sa'],
            ['name' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa'],
            ['name' => 'Snapchat', 'link' => 'https://snapchat.com/add/zadon_sa'],
            ['name' => 'WhatsApp', 'link' => 'https://wa.me/966500000000'],
            ['name' => 'TikTok', 'link' => 'https://tiktok.com/@zadon_sa'],
        ];

        foreach ($socials as $social) {
            Social::create($social);
        }
    }
}
