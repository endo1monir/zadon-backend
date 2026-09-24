<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $balances = [
            'abdullah@zadon.sa' => 250.00,
            'fatima@zadon.sa' => 320.50,
            'noura@zadon.sa' => 31.05,
            'demo@zadon.sa' => 150.00,
        ];

        foreach (User::all() as $user) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $balances[$user->email] ?? 0.00,
            ]);
        }
    }
}
