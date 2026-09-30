<?php

use App\Http\Resources\StoreResource;
use App\Models\Category;
use App\Models\City;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->city = City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'is_active' => true, 'sort_order' => 0]);
    $this->category = Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'is_active' => true,
        'sort_order' => 0,
    ]);
});

test('store returns name and address in english by default', function () {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'name_en' => 'Al-Aila Pharmacy',
        'city_id' => $this->city->id,
        'category_id' => $this->category->id,
        'address' => 'Riyadh - King Fahd Road',
    ]);

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.name', 'Al-Aila Pharmacy')
        ->assertJsonPath('data.featured_stores.0.address', 'Riyadh - King Fahd Road')
        ->assertJsonPath('data.featured_stores.0.city.name', 'Riyadh');
});

test('store name follows the lang header and address stays as stored', function () {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'name_en' => 'Al-Aila Pharmacy',
        'city_id' => $this->city->id,
        'category_id' => $this->category->id,
        'address' => 'Riyadh - King Fahd Road',
    ]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.name', 'صيدلية العائلة')
        ->assertJsonPath('data.featured_stores.0.address', 'Riyadh - King Fahd Road')
        ->assertJsonPath('data.featured_stores.0.city.name', 'الرياض');
});

test('status is translated to status_text in english', function (string $status, string $expected) {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'city_id' => $this->city->id,
        'category_id' => $this->category->id,
        'address' => 'Riyadh - King Fahd Road',
        'status' => $status,
    ]);

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.status', $status)
        ->assertJsonPath('data.featured_stores.0.status_text', $expected);
})->with([
    ['open', 'Open'],
    ['busy', 'Busy'],
    ['closed', 'Closed'],
]);

test('status is translated to status_text in arabic', function (string $status, string $expected) {
    Store::create([
        'name_ar' => 'صيدلية العائلة',
        'city_id' => $this->city->id,
        'category_id' => $this->category->id,
        'address' => 'Riyadh - King Fahd Road',
        'status' => $status,
    ]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.featured_stores.0.status', $status)
        ->assertJsonPath('data.featured_stores.0.status_text', $expected);
})->with([
    ['open', 'مفتوح'],
    ['busy', 'مشغول'],
    ['closed', 'مغلق'],
]);

test('status_text is null when the store has no status', function () {
    // `stores.status` is NOT NULL, so this state is only reachable on an
    // in-memory model. It must still not leak a raw translation key.
    $store = new Store(['name_ar' => 'صيدلية العائلة', 'status' => null]);
    $store->created_at = now();
    $store->updated_at = now();

    $data = (new StoreResource($store))->toArray(request());

    expect($data['status'])->toBeNull()
        ->and($data['status_text'])->toBeNull();
});
