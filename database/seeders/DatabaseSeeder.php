<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $tables = [
            'wallet_transactions',
            'user_notifications',
            'stock_adjustments',
            'reviews',
            'order_items',
            'orders',
            'cart_items',
            'carts',
            'products',
            'addresses',
            'wallets',
            'stores',
            'categories',
            'cities',
            'contact_messages',
            'socials',
            'settings',
            'banners',
            'users',
        ];

        Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        $this->call([
            CitySeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            StoreSeeder::class,
            AddressSeeder::class,
            ProductSeeder::class,
            WalletSeeder::class,
            CartSeeder::class,
            OrderSeeder::class,
            StockAdjustmentSeeder::class,
            ReviewSeeder::class,
            UserNotificationSeeder::class,
            WalletTransactionSeeder::class,
            SocialSeeder::class,
            SettingsSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
