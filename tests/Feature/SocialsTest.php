<?php

use App\Models\Social;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('socials are listed for guests', function () {
    Social::create(['name' => 'X (Twitter)', 'link' => 'https://x.com/zadon_sa']);
    Social::create(['name' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa']);

    $this->getJson('/api/socials')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['success', 'message', 'data' => ['socials' => [['id', 'name', 'icon', 'link']]]])
        ->assertJsonPath('data.socials.0.name', 'X (Twitter)')
        ->assertJsonPath('data.socials.0.icon', null);
});

test('socials are ordered by id', function () {
    Social::create(['name' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa']);
    Social::create(['name' => 'X (Twitter)', 'link' => 'https://x.com/zadon_sa']);

    $this->getJson('/api/socials')
        ->assertOk()
        ->assertJsonPath('data.socials.0.name', 'Instagram')
        ->assertJsonPath('data.socials.1.name', 'X (Twitter)');
});

test('authenticated user can upload a social icon', function () {
    Storage::fake('public');

    $user = User::create(['name' => 'مدير', 'phone' => '0557777777', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    $social = Social::create(['name' => 'X (Twitter)', 'link' => 'https://x.com/zadon_sa']);

    $this->post('/api/socials/'.$social->id.'/icon', [
        'icon' => UploadedFile::fake()->create('x.png', 100, 'image/png'),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Social icon uploaded successfully.')
        ->assertJsonStructure(['data' => ['social' => ['id', 'name', 'icon', 'link']]])
        ->assertJsonPath('data.social.icon', fn ($icon) => str_contains($icon, '/storage/socials/'));

    $social->refresh();
    $this->assertNotNull($social->icon);
    $this->assertStringStartsWith('socials/', $social->icon);

    Storage::disk('public')->assertExists($social->icon);
});

test('icon upload requires an image file', function () {
    $user = User::create(['name' => 'مدير', 'phone' => '0558888888', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    $social = Social::create(['name' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa']);

    $this->post('/api/socials/'.$social->id.'/icon', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['icon']);
});

test('icon upload requires authentication', function () {
    $social = Social::create(['name' => 'Instagram', 'link' => 'https://instagram.com/zadon_sa']);

    $this->postJson('/api/socials/'.$social->id.'/icon', ['icon' => 'x'])
        ->assertStatus(401);
});
