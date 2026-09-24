<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('login message is translated to arabic via lang header', function () {
    $this->withHeader('lang', 'ar')
        ->postJson('/api/auth/login', ['phone' => '0556666666'])
        ->assertOk()
        ->assertJsonPath('message', 'تم إرسال رمز التحقق إلى هاتفك.');
});

test('login creates a new user and sends an otp when phone is not registered', function () {
    $response = $this->postJson('/api/auth/login', ['phone' => '0550000000']);

    $response->assertOk()
        ->assertJsonPath('data.user.phone', '0550000000')
        ->assertJsonPath('data.user.is_completed', false)
        ->assertJsonStructure(['success', 'message', 'data' => ['code', 'user' => ['id', 'phone']]]);

    $this->assertDatabaseHas('users', ['phone' => '0550000000', 'is_completed' => false]);

    $this->assertDatabaseHas('users', [
        'phone' => '0550000000',
        'code' => $response->json('data.code'),
    ]);
});

test('login does not create a duplicate user for an existing phone', function () {
    User::create(['name' => 'عميل', 'phone' => '0551111111', 'role' => 'customer']);

    $this->postJson('/api/auth/login', ['phone' => '0551111111'])
        ->assertOk()
        ->assertJsonStructure(['success', 'data' => ['code']]);

    $this->assertDatabaseCount('users', 1);
});

test('verify otp issues a token and returns is_completed false for a new user', function () {
    $login = $this->postJson('/api/auth/login', ['phone' => '0552222222'])->json();

    $verify = $this->postJson('/api/auth/verify-otp', [
        'phone' => '0552222222',
        'otp' => (string) $login['data']['code'],
    ]);

    $verify->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_completed', false)
        ->assertJsonPath('data.user.phone', '0552222222')
        ->assertJsonPath('data.user.is_completed', false)
        ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user' => ['id', 'phone', 'is_completed']]]);

    $this->assertDatabaseHas('users', ['phone' => '0552222222', 'phone_verified_at' => now()]);

    $this->assertDatabaseHas('users', ['phone' => '0552222222', 'code' => null]);
    $this->assertDatabaseHas('wallets', ['user_id' => User::where('phone', '0552222222')->first()->id]);
});

test('verify otp returns is_completed true for a user who already completed the profile', function () {
    User::create([
        'name' => 'عميل مكتمل',
        'phone' => '0553333333',
        'role' => 'customer',
        'is_completed' => true,
    ]);

    $login = $this->postJson('/api/auth/login', ['phone' => '0553333333'])->json();

    $verify = $this->postJson('/api/auth/verify-otp', [
        'phone' => '0553333333',
        'otp' => (string) $login['data']['code'],
    ]);

    $verify->assertOk()
        ->assertJsonPath('data.is_completed', true)
        ->assertJsonPath('data.user.is_completed', true);
});

test('verify otp rejects an invalid otp', function () {
    $this->postJson('/api/auth/login', ['phone' => '0554444444']);

    $this->postJson('/api/auth/verify-otp', [
        'phone' => '0554444444',
        'otp' => '000000',
    ])->assertStatus(400)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'These credentials do not match our records.');
});

test('verify otp stores and returns the fcm token', function () {
    $login = $this->postJson('/api/auth/login', ['phone' => '0556666667'])->json();

    $this->postJson('/api/auth/verify-otp', [
        'phone' => '0556666667',
        'otp' => (string) $login['data']['code'],
        'fcm_token' => 'device-fcm-token-123',
    ])->assertOk()
        ->assertJsonPath('data.fcm_token', 'device-fcm-token-123')
        ->assertJsonStructure(['success', 'message', 'data' => ['token', 'fcm_token', 'user', 'is_completed']]);

    $this->assertDatabaseHas('personal_access_tokens', ['fcm_token' => 'device-fcm-token-123']);
});

test('complete profile sets is_completed true for an authenticated user', function () {
    $user = User::create(['name' => null, 'phone' => '0555555555', 'role' => 'customer']);
    $city = City::create(['name_ar' => 'الرياض', 'name_en' => 'Riyadh', 'sort_order' => 0]);

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/auth/complete-profile', [
        'name' => 'عميل جديد',
        'email' => 'customer@zadon.sa',
        'city_id' => $city->id,
    ])->assertOk()
        ->assertJsonPath('data.user.is_completed', true)
        ->assertJsonPath('data.user.name', 'عميل جديد')
        ->assertJsonPath('data.user.city.name', 'Riyadh');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_completed' => true, 'city_id' => $city->id]);
});
