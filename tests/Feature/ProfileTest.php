<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('profile update persists the submitted fields', function () {
    $user = User::create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'phone' => '0551111111',
        'role' => 'customer',
    ]);
    $city = City::create(['name_ar' => 'جدة', 'name_en' => 'Jeddah', 'sort_order' => 1]);

    Sanctum::actingAs($user, ['app']);

    $this->patchJson('/api/profile', [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => '0552222222',
        'city_id' => $city->id,
    ])->assertOk()
        ->assertJsonPath('data.user.name', 'New Name')
        ->assertJsonPath('data.user.email', 'new@example.com')
        ->assertJsonPath('data.user.phone', '0552222222')
        ->assertJsonPath('data.user.city.id', $city->id);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => '0552222222',
        'city_id' => $city->id,
    ]);
});

test('profile updates accept an avatar and preserve omitted fields', function () {
    Storage::fake('public');
    $user = User::factory()->create(['name' => 'Original Name']);
    Sanctum::actingAs($user, ['app']);

    $this->post('/api/profile', [
        '_method' => 'PATCH',
        'avatar' => UploadedFile::fake()->createWithContent('avatar.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/aWQAAAAASUVORK5CYII=')),
    ], ['Accept' => 'application/json'])->assertOk()
        ->assertJsonPath('data.user.name', 'Original Name');

    $user->refresh();
    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});

test('profile updates reject another users email without changing data', function () {
    $user = User::factory()->create(['name' => 'Original Name']);
    $other = User::factory()->create();
    Sanctum::actingAs($user, ['app']);

    $this->patchJson('/api/profile', [
        'name' => 'Changed Name',
        'email' => $other->email,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Original Name']);
});
