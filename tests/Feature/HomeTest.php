<?php

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('home returns banners', function () {
    Banner::create(['image' => 'https://placehold.co/1200x400?text=Zadon']);
    Banner::create(['image' => 'banners/slide.png']);

    $this->getJson('/api/home')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'banners' => [['id', 'image']],
                'categories',
                'featured_stores',
            ],
        ])
        ->assertJsonPath('data.banners.0.image', 'https://placehold.co/1200x400?text=Zadon')
        ->assertJsonPath('data.banners.1.image', fn ($image) => str_contains($image, '/storage/banners/slide.png'));
});
