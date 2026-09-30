<?php

use App\Models\Category;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->vendor = User::create([
        'name' => 'Pharmacy Owner',
        'phone' => '0555123456',
        'email' => 'vendor@example.com',
        'password' => 'password123',
        'role' => 'vendor',
    ]);

    $category = Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $city = City::create([
        'name_ar' => 'مكة المكرمة',
        'name_en' => 'Mecca',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->vendor->stores()->create([
        'name_ar' => 'صيدلية النور',
        'name_en' => 'Al Noor Pharmacy',
        'category_id' => $category->id,
        'city_id' => $city->id,
        'address' => 'جدة، حي الشاطئ',
        'is_verified' => true,
    ]);
});

test('a vendor logs in successfully', function () {
    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['success', 'message', 'data' => ['token', 'fcm_token', 'user', 'stores']]);
});

test('login stores the fcm token on the generated token row', function () {
    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'password123',
        'fcm_token' => 'vendor-device-fcm-token-123',
    ])->assertOk()
        ->assertJsonPath('data.fcm_token', 'vendor-device-fcm-token-123');

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => User::class,
        'tokenable_id' => $this->vendor->id,
        'name' => 'vendor',
        'fcm_token' => 'vendor-device-fcm-token-123',
    ]);
});

test('login returns a null fcm token when none is sent', function () {
    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'password123',
    ])->assertOk()
        ->assertJsonPath('data.fcm_token', null);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $this->vendor->id,
        'fcm_token' => null,
    ]);
});

test('an fcm token sent on login does not leak onto the previous token row', function () {
    $existing = $this->vendor->createToken('vendor', ['vendor']);

    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'password123',
        'fcm_token' => 'new-device-token',
    ])->assertOk();

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $existing->accessToken->id,
        'fcm_token' => null,
    ]);
    $this->assertDatabaseHas('personal_access_tokens', ['fcm_token' => 'new-device-token']);
});

test('login rejects an fcm token that is too long', function () {
    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'password123',
        'fcm_token' => str_repeat('a', 256),
    ])->assertStatus(422)
        ->assertJsonValidationErrors('fcm_token');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('login fails with bad credentials and stores no token', function () {
    $this->postJson('/api/vendor/auth/login', [
        'phone' => '0555123456',
        'password' => 'wrong-password',
        'fcm_token' => 'vendor-device-fcm-token-123',
    ])->assertStatus(400);

    $this->assertDatabaseCount('personal_access_tokens', 0);
});
