<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;

class WalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $walletOf = fn (string $email) => User::where('email', $email)->firstOrFail()->wallet;

        $transactions = [
            [
                'wallet' => $walletOf('abdullah@zadon.sa'),
                'type' => 'credit',
                'amount' => 250.00,
                'balance_after' => 250.00,
                'description' => 'تحميل رصيد أولي',
            ],
            [
                'wallet' => $walletOf('fatima@zadon.sa'),
                'type' => 'credit',
                'amount' => 320.50,
                'balance_after' => 320.50,
                'description' => 'تحميل رصيد أولي',
            ],
            [
                'wallet' => $walletOf('noura@zadon.sa'),
                'type' => 'credit',
                'amount' => 100.00,
                'balance_after' => 100.00,
                'description' => 'تحميل رصيد',
            ],
            [
                'wallet' => $walletOf('noura@zadon.sa'),
                'type' => 'debit',
                'amount' => 68.95,
                'balance_after' => 31.05,
                'description' => 'دفع طلب بالرصيد',
                'reference' => Order::where('order_number', 'ZDN-2026-0004')->firstOrFail(),
            ],
        ];

        foreach ($transactions as $transaction) {
            $reference = $transaction['reference'] ?? null;
            $walletId = $transaction['wallet']->id;
            $userId = $transaction['wallet']->user_id;

            unset($transaction['reference'], $transaction['wallet']);

            WalletTransaction::create(array_merge($transaction, [
                'wallet_id' => $walletId,
                'user_id' => $userId,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
            ]));
        }
    }
}
