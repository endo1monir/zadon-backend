<?php

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('shows a newly uploaded banner on the home endpoint', function (): void {
    actingAsAdmin();
    Storage::fake('public');

    // warm the cache first
    $this->getJson('/api/home')->assertOk();

    $this->post(route('admin.banners.store'), [
        'image' => UploadedFile::fake()->create('hero.jpg', 100, 'image/jpeg'),
    ])->assertSessionHas('success');

    $banner = Banner::sole();

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.banners.0.id', $banner->id);
});

it('reflects a banner image replacement on the home endpoint', function (): void {
    actingAsAdmin();
    Storage::fake('public');

    $banner = Banner::factory()->create(['image' => 'banners/old.jpg']);
    Storage::disk('public')->put('banners/old.jpg', 'old');

    $this->getJson('/api/home')->assertOk();

    $this->put(route('admin.banners.update', $banner), [
        'image' => UploadedFile::fake()->create('new.png', 80, 'image/png'),
    ])->assertSessionHas('success');

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('data.banners.0.id', $banner->id);
});

it('removes a deleted banner from the home endpoint', function (): void {
    actingAsAdmin();
    Storage::fake('public');

    $banner = Banner::factory()->create(['image' => 'banners/gone.jpg']);
    Storage::disk('public')->put('banners/gone.jpg', 'gone');

    $this->getJson('/api/home')->assertOk()
        ->assertJsonCount(1, 'data.banners');

    $this->delete(route('admin.banners.destroy', $banner))->assertSessionHas('success');

    $this->getJson('/api/home')->assertOk()->assertJsonCount(0, 'data.banners');
});

it('forgets the banners cache key when a banner changes', function (): void {
    actingAsAdmin();
    Storage::fake('public');

    Cache::put('home:banners', 'stale', now()->addHours(6));

    Banner::factory()->create();

    expect(Cache::has('home:banners'))->toBeFalse();
});
