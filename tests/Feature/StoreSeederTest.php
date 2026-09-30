<?php

use App\Models\Store;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\StoreSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the store seeder fills the city and single address columns', function () {
    $this->seed([CitySeeder::class, UserSeeder::class, CategorySeeder::class, StoreSeeder::class]);

    $store = Store::where('name_en', 'Zadon Pharmacy')->firstOrFail();

    $this->assertNotNull($store->city_id);
    $this->assertSame('الرياض', $store->city->name_ar);
    $this->assertStringContainsString('طريق الملك فهد', $store->address);
    $this->assertNotEmpty(Store::whereNotNull('city_id')->count());
});
