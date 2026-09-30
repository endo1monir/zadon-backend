<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->category = Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->city = City::create([
        'name_ar' => 'مكة المكرمة',
        'name_en' => 'Mecca',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->store = [
        'name_ar' => 'صيدلية النور',
        'name_en' => 'Al Noor Pharmacy',
        'category_id' => $this->category->id,
        'city_id' => $this->city->id,
        'address' => 'الرياض، حي العليا، شارع الملك فهد',
        'phone' => '0112345678',
        'cr_number' => '1010123456',
        'vat_number' => '310121234500003',
        'manager_name' => 'Ali Hassan',
        'prep_time_min' => 30,
        'delivery_fee' => 30,
        'min_order' => 30,
        'is_open_24_7' => true,
        'delivery_radius_km' => 10,
    ];

    $this->payload = [
        'name' => 'Pharmacy Owner',
        'phone' => '0555123456',
        'email' => 'vendor@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'store' => $this->store,
    ];
});

test('a vendor can register and the store is created with the remaining data', function () {
    $response = $this->post('/api/vendor/auth/register', $this->payload);

    $response->assertCreated()
        ->assertJsonPath('data.user.name', 'Pharmacy Owner')
        ->assertJsonPath('data.user.role', 'vendor')
        ->assertJsonPath('data.store.name', 'Al Noor Pharmacy')
        ->assertJsonPath('data.store.name_ar', 'صيدلية النور')
        ->assertJsonPath('data.store.address', 'الرياض، حي العليا، شارع الملك فهد')
        ->assertJsonPath('data.store.cr_number', '1010123456')
        ->assertJsonPath('data.store.vat_number', '310121234500003')
        ->assertJsonPath('data.store.manager_name', 'Ali Hassan')
        ->assertJsonPath('data.store.prep_time_min', '30 - 40')
        ->assertJsonPath('data.store.delivery_fee', 30)
        ->assertJsonPath('data.store.min_order', 30)
        ->assertJsonPath('data.store.is_open_24_7', true)
        ->assertJsonPath('data.store.delivery_radius_km', 10)
        ->assertJsonPath('data.store.status', 'open')
        ->assertJsonPath('data.store.is_active', true)
        ->assertJsonPath('data.store.rating_count', 0)
        ->assertJsonPath('data.store.city.id', $this->city->id)
        ->assertJsonPath('data.store.category.id', $this->category->id);

    $this->assertDatabaseHas('users', [
        'name' => 'Pharmacy Owner',
        'phone' => '0555123456',
        'email' => 'vendor@example.com',
        'role' => 'vendor',
    ]);

    $store = Store::firstOrFail();

    $this->assertSame(User::where('phone', '0555123456')->firstOrFail()->id, $store->owner_id);
    $this->assertTrue($store->is_verified);
});

test('registration returns full paths for the uploaded logo and cover image', function () {
    $this->payload['store']['logo'] = UploadedFile::fake()->create('logo.png', 10, 'image/png');
    $this->payload['store']['cover_image'] = UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg');

    $response = $this->post('/api/vendor/auth/register', $this->payload)->assertCreated();

    $logo = $response->json('data.store.logo');
    $cover = $response->json('data.store.cover_image');

    $this->assertStringStartsWith('http', $logo);
    $this->assertStringStartsWith('http', $cover);

    $store = Store::firstOrFail();

    Storage::disk('public')->assertExists($store->logo);
    Storage::disk('public')->assertExists($store->cover_image);

    $this->assertStringEndsWith($store->logo, $logo);
    $this->assertStringEndsWith($store->cover_image, $cover);
});

test('the image paths also appear in the stores collection', function () {
    $this->payload['store']['logo'] = UploadedFile::fake()->create('logo.png', 10, 'image/png');

    $response = $this->post('/api/vendor/auth/register', $this->payload)->assertCreated();

    $logo = $response->json('data.stores.0.logo');

    $this->assertStringStartsWith('http', $logo);
    $this->assertStringEndsWith(Store::firstOrFail()->logo, $logo);
});

test('registration rejects a non image logo', function () {
    $this->payload['store']['logo'] = UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf');

    $this->post('/api/vendor/auth/register', $this->payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('store.logo');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('stores', 0);
});

test('registration requires an existing city', function () {
    $this->payload['store']['city_id'] = 9999;

    $this->post('/api/vendor/auth/register', $this->payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('store.city_id');

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('stores', 0);
});

test('registration requires the store address', function () {
    unset($this->payload['store']['address']);

    $this->post('/api/vendor/auth/register', $this->payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('store.address');

    $this->assertDatabaseCount('stores', 0);
});

test('a failed store insert does not leave an orphan vendor user', function () {
    DB::statement('DROP TABLE stores');

    $this->post('/api/vendor/auth/register', $this->payload)->assertStatus(500);

    $this->assertDatabaseCount('users', 0);
});

test('a prep time range is accepted and stored as its lower bound', function (string|int $input, int $expected) {
    $this->payload['store']['prep_time_min'] = $input;

    $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->assertJsonPath('data.store.prep_time_min', $expected.' - '.($expected + 10));

    expect(Store::firstOrFail()->prep_time_min)->toBe($expected);
})->with([
    'spaced range' => ['30 - 40', 30],
    'compact range' => ['30-40', 30],
    'plain integer' => [30, 30],
    'integer as string' => ['30', 30],
]);

test('the store is reachable from the vendor token', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->assertNotNull($token);

    $this->withToken($token)
        ->getJson('/api/vendor/auth/me')
        ->assertOk()
        ->assertJsonPath('data.store.address', 'الرياض، حي العليا، شارع الملك فهد')
        ->assertJsonPath('data.store.city_id', $this->city->id);
});

test('the vendor can update the store with the new address and city keys', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $city = City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'is_active' => true, 'sort_order' => 1]);

    $this->withToken($token)
        ->putJson('/api/vendor/store', [
            'city_id' => $city->id,
            'address' => 'جدة، حي الشاطئ',
            'delivery_fee' => 20,
        ])
        ->assertOk()
        ->assertJsonPath('data.store.address', 'جدة، حي الشاطئ')
        ->assertJsonPath('data.store.city.id', $city->id)
        ->assertJsonPath('data.store.delivery_fee', 20);
});

test('a prep time range read from the store can be sent straight back', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $current = $this->withToken($token)->getJson('/api/vendor/store')->json('data.store');

    $this->withToken($token)
        ->putJson('/api/vendor/store', [
            'address' => 'الرياض، حي العليا، شارع الملك فهد',
            'prep_time_min' => $current['prep_time_min'],
        ])
        ->assertOk()
        ->assertJsonPath('data.store.prep_time_min', $current['prep_time_min']);
});

test('the store can be updated with the same nested keys used at registration', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $city = City::create(['name_ar' => 'جدة', 'name_en' => 'Jeddah', 'is_active' => true, 'sort_order' => 1]);

    $response = $this->withToken($token)
        ->putJson('/api/vendor/store', [
            'store' => [
                'name_ar' => 'صيدلية الشفاء',
                'name_en' => 'Al Shifa Pharmacy',
                'category_id' => $this->category->id,
                'city_id' => $city->id,
                'address' => 'جدة، حي الشاطئ، شارع التحلية',
                'phone' => '0112345678',
                'cr_number' => '1010123456',
                'vat_number' => '310121234500003',
                'manager_name' => 'Ali Hassan',
                'prep_time_min' => 30,
                'delivery_fee' => 30,
                'min_order' => 30,
                'is_open_24_7' => true,
                'delivery_radius_km' => 1,
            ],
        ])
        ->assertOk()
        ->assertJsonPath('data.store.name_ar', 'صيدلية الشفاء')
        ->assertJsonPath('data.store.name_en', 'Al Shifa Pharmacy')
        ->assertJsonPath('data.store.city_id', $city->id)
        ->assertJsonPath('data.store.city.name', 'Jeddah')
        ->assertJsonPath('data.store.category_id', $this->category->id)
        ->assertJsonPath('data.store.address', 'جدة، حي الشاطئ، شارع التحلية')
        ->assertJsonPath('data.store.phone', '0112345678')
        ->assertJsonPath('data.store.cr_number', '1010123456')
        ->assertJsonPath('data.store.vat_number', '310121234500003')
        ->assertJsonPath('data.store.manager_name', 'Ali Hassan')
        ->assertJsonPath('data.store.delivery_fee', 30)
        ->assertJsonPath('data.store.min_order', 30)
        ->assertJsonPath('data.store.is_open_24_7', true)
        ->assertJsonPath('data.store.delivery_radius_km', 1)
        ->assertJsonStructure(['data' => ['store' => ['id', 'name_ar', 'city_id', 'address']]]);

    // The `store` wrapper must never be written as a column.
    $this->assertDatabaseMissing('stores', ['store' => []]);
    expect(Store::firstOrFail()->name_ar)->toBe('صيدلية الشفاء');
    $response->assertJsonMissingPath('data.store.store');
});

test('nested and flat update keys both work and the flat shape still passes', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->withToken($token)
        ->putJson('/api/vendor/store', ['address' => 'flat address'])
        ->assertOk()
        ->assertJsonPath('data.store.address', 'flat address');

    $this->withToken($token)
        ->putJson('/api/vendor/store', ['store' => ['address' => 'nested address']])
        ->assertOk()
        ->assertJsonPath('data.store.address', 'nested address');
});

test('nested keys win when both shapes are sent together', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->withToken($token)
        ->putJson('/api/vendor/store', [
            'address' => 'flat address',
            'store' => ['address' => 'nested address'],
        ])
        ->assertOk()
        ->assertJsonPath('data.store.address', 'nested address');
});

test('logo and cover image can be uploaded with the nested keys', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->call('PUT', '/api/vendor/store', [
        'store' => [
            'name_ar' => 'صيدلية النور',
            'address' => 'الرياض، حي العليا، شارع الملك فهد',
        ],
    ], [], [
        'store' => [
            'logo' => UploadedFile::fake()->create('logo.png', 10, 'image/png'),
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
        ],
    ], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
        'HTTP_ACCEPT' => 'application/json',
    ])->assertOk()
        ->assertJsonPath('data.store.name_ar', 'صيدلية النور');

    $store = Store::firstOrFail();

    Storage::disk('public')->assertExists($store->logo);
    Storage::disk('public')->assertExists($store->cover_image);

    $this->assertIsString($store->logo, 'logo type: '.get_debug_type($store->logo));
    $this->assertStringStartsWith('stores/', $store->logo);
    $this->assertStringStartsWith('stores/', $store->cover_image);
});

test('nested updates are still validated', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->withToken($token)
        ->putJson('/api/vendor/store', [
            'store' => [
                'city_id' => 999999,
                'delivery_radius_km' => 5000,
                'prep_time_min' => 900,
            ],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['city_id', 'delivery_radius_km', 'prep_time_min']);
});

test('a prep time range is accepted through the nested keys', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->withToken($token)
        ->putJson('/api/vendor/store', ['store' => ['prep_time_min' => '45 - 55']])
        ->assertOk()
        ->assertJsonPath('data.store.prep_time_min', '45 - 55');

    expect(Store::firstOrFail()->prep_time_min)->toBe(45);
});

test('a non-array store key is rejected', function () {
    $token = $this->post('/api/vendor/auth/register', $this->payload)
        ->assertCreated()
        ->json('data.token');

    $this->withToken($token)
        ->putJson('/api/vendor/store', ['store' => 'not-an-array'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('store');
});
