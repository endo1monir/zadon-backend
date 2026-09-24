<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('authenticated user can send a contact message', function () {
    $user = User::create(['name' => 'عميل', 'phone' => '0551111111', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/contact-us', [
        'title' => 'استفسار',
        'message' => 'أريد معرفة حالة طلبي.',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Your message has been sent successfully.')
        ->assertJsonStructure(['success', 'message', 'data' => ['id']]);

    $this->assertDatabaseHas('contact_messages', [
        'user_id' => $user->id,
        'title' => 'استفسار',
        'message' => 'أريد معرفة حالة طلبي.',
    ]);
});

test('contact message requires title and message', function () {
    $user = User::create(['name' => 'عميل', 'phone' => '0552222222', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/contact-us', ['title' => 'استفسار'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['message']);

    $this->postJson('/api/contact-us', ['message' => 'رسالة'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title']);

    $this->assertDatabaseCount('contact_messages', 0);
});

test('contact us requires authentication', function () {
    $this->postJson('/api/contact-us', [
        'title' => 'استفسار',
        'message' => 'أريد معرفة حالة طلبي.',
    ])->assertStatus(401);
});
