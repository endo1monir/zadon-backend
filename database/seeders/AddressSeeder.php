<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            ['email' => 'abdullah@zadon.sa', 'title' => 'home', 'latitude' => 24.7136000, 'longitude' => 46.6753000, 'is_default' => true],
            ['email' => 'fatima@zadon.sa', 'title' => 'home', 'latitude' => 21.4858000, 'longitude' => 39.1925000, 'is_default' => true],
            ['email' => 'mohammed@zadon.sa', 'title' => 'home', 'latitude' => 24.6389000, 'longitude' => 46.6932000, 'is_default' => false],
            ['email' => 'noura@zadon.sa', 'title' => 'work',  'latitude' => 24.7227000, 'longitude' => 46.6755000, 'is_default' => false],
            ['email' => 'yousef@zadon.sa', 'title' => 'home',  'latitude' => 26.4333000, 'longitude' => 50.0869000, 'is_default' => true],
            ['email' => 'demo@zadon.sa', 'title' => 'home', 'latitude' => 24.6111000, 'longitude' => 46.6508000, 'is_default' => false],
        ];

        foreach ($addresses as $address) {
            $user = User::where('email', $address['email'])->firstOrFail();
            unset($address['email']);

            Address::create(array_merge($address, [
                'user_id' => $user->id,
                'full_address' => "{$address['title']} Address for {$user->name}",
            ]));
        }
    }
}
