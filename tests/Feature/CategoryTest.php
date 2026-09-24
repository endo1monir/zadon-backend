<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('category icon returns the full path', function () {
    Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'icon' => 'categories/pharmacies.png',
        'sort_order' => 0,
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.categories.0.icon', fn ($icon) => str_contains($icon, '/storage/categories/pharmacies.png'));
});

test('category icon url is returned as-is when it is an absolute url', function () {
    Category::create([
        'type' => 'store',
        'name_ar' => 'سوبر ماركت',
        'name_en' => 'Supermarkets',
        'slug' => 'supermarkets',
        'icon' => 'https://placehold.co/200x200?text=Supermarkets',
        'sort_order' => 0,
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.categories.0.icon', 'https://placehold.co/200x200?text=Supermarkets');
});

test('category name follows the lang header', function () {
    Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'sort_order' => 0,
    ]);

    $this->withHeader('lang', 'ar')
        ->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.categories.0.name', 'صيدليات');
});

test('category name is returned in english by default', function () {
    Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'sort_order' => 0,
    ]);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonPath('data.categories.0.name', 'Pharmacies');
});

test('authenticated user can upload a category icon', function () {
    Storage::fake('public');

    $user = User::create(['name' => 'مدير', 'phone' => '0559999999', 'role' => 'customer']);
    Sanctum::actingAs($user, ['app']);

    $category = Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'sort_order' => 0,
    ]);

    $this->post('/api/categories/'.$category->id.'/icon', [
        'icon' => UploadedFile::fake()->create('pharmacies.png', 100, 'image/png'),
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Category icon uploaded successfully.')
        ->assertJsonStructure(['data' => ['category' => ['id', 'icon']]])
        ->assertJsonPath('data.category.icon', fn ($icon) => str_contains($icon, '/storage/categories/'));

    $category->refresh();
    $this->assertNotNull($category->icon);
    $this->assertStringStartsWith('categories/', $category->icon);

    Storage::disk('public')->assertExists($category->icon);
});

test('category icon upload requires authentication', function () {
    $category = Category::create([
        'type' => 'store',
        'name_ar' => 'صيدليات',
        'name_en' => 'Pharmacies',
        'slug' => 'pharmacies',
        'sort_order' => 0,
    ]);

    $this->postJson('/api/categories/'.$category->id.'/icon', ['icon' => 'x'])
        ->assertStatus(401);
});
