<?php

use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cities are listed for guests', function () {
    City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'sort_order' => 0]);

    $this->getJson('/api/cities')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['success', 'message', 'data' => ['cities' => [['id', 'name']]]])
        ->assertJsonPath('data.cities.0.name', 'Riyadh');
});

test('city name follows the lang header', function () {
    City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'sort_order' => 0]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/cities')
        ->assertOk()
        ->assertJsonPath('data.cities.0.name', 'الرياض');
});

test('inactive cities are not listed', function () {
    City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'sort_order' => 0]);
    City::create(['name_ar' => 'جدة', 'name_en' => 'Jeddah', 'is_active' => false, 'sort_order' => 1]);

    $this->getJson('/api/cities')
        ->assertOk()
        ->assertJsonCount(1, 'data.cities');
});
