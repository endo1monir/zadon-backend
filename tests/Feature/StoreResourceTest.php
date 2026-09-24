<?php

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store returns name and address in english by default', function () {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'name_en' => 'Al-Aila Pharmacy',
        'city' => 'الرياض',
        'address_ar' => 'الرياض - طريق الملك فهد',
        'address_en' => 'Riyadh - King Fahd Road',
    ]);

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.name', 'Al-Aila Pharmacy')
        ->assertJsonPath('data.featured_stores.0.address', 'Riyadh - King Fahd Road');
});

test('store name and address follow the lang header', function () {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'name_en' => 'Al-Aila Pharmacy',
        'city' => 'الرياض',
        'address_ar' => 'الرياض - طريق الملك فهد',
        'address_en' => 'Riyadh - King Fahd Road',
    ]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.name', 'صيدلية العائلة')
        ->assertJsonPath('data.featured_stores.0.address', 'الرياض - طريق الملك فهد');
});
