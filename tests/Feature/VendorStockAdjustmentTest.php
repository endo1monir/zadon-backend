<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

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

    $this->store = $this->vendor->stores()->create([
        'name_ar' => 'صيدلية النور',
        'name_en' => 'Al Noor Pharmacy',
        'category_id' => $category->id,
        'city_id' => $city->id,
        'address' => 'جدة، حي الشاطئ',
        'is_verified' => true,
    ]);

    $this->product = Product::create([
        'store_id' => $this->store->id,
        'category_id' => $category->id,
        'name_ar' => 'فيتامين ج',
        'name_en' => 'Vitamin C',
        'sku' => 'VIT-C',
        'price' => 25,
        'stock' => 600,
    ]);

    $this->endpoint = "/api/vendor/products/{$this->product->id}/stock-adjustments";

    Sanctum::actingAs($this->vendor, ['vendor']);
});

test('a stock adjustment without an explicit type is recorded and applied', function () {
    $this->postJson($this->endpoint, [
        'delta' => 540,
        'reason' => 'توريد دفعة',
    ])->assertCreated()
        ->assertJsonPath('data.adjustment.type', 'restock')
        ->assertJsonPath('data.adjustment.quantity', 540)
        ->assertJsonPath('data.adjustment.previous_stock', 600)
        ->assertJsonPath('data.adjustment.new_stock', 1140)
        ->assertJsonPath('data.adjustment.reason', 'توريد دفعة');

    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock' => 1140]);

    expect(StockAdjustment::where('product_id', $this->product->id)->count())->toBe(1);
});

test('a negative delta defaults to a correction type', function () {
    $this->postJson($this->endpoint, ['delta' => -20])
        ->assertCreated()
        ->assertJsonPath('data.adjustment.type', 'correction');

    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock' => 580]);
});

test('an explicit type and reason are persisted as sent', function () {
    $this->postJson($this->endpoint, [
        'delta' => -10,
        'type' => 'waste',
        'reason' => 'انتهاء صلاحية',
    ])->assertCreated()
        ->assertJsonPath('data.adjustment.type', 'waste')
        ->assertJsonPath('data.adjustment.reason', 'انتهاء صلاحية');
});

test('an unknown type is rejected and nothing changes', function () {
    $this->postJson($this->endpoint, ['delta' => 5, 'type' => 'shrinkage'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('type');

    $this->assertDatabaseCount('stock_adjustments', 0);
    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock' => 600]);
});

test('stock is never pushed below zero and the real delta is recorded', function () {
    $this->postJson($this->endpoint, ['delta' => -900])
        ->assertCreated()
        ->assertJsonPath('data.adjustment.new_stock', 0)
        ->assertJsonPath('data.adjustment.quantity', -900);

    $this->assertDatabaseHas('products', ['id' => $this->product->id, 'stock' => 0]);
});

test('a vendor cannot adjust a product belonging to another store', function () {
    $other = User::create([
        'name' => 'Other Owner',
        'phone' => '0555000000',
        'email' => 'other@example.com',
        'password' => 'password123',
        'role' => 'vendor',
    ]);

    $foreign = $other->stores()->create([
        'name_ar' => 'صيدلية أخرى',
        'name_en' => 'Other Pharmacy',
        'category_id' => $this->product->category_id,
        'city_id' => $this->store->city_id,
        'address' => 'جدة، حي آخر',
    ]);

    $foreignProduct = Product::create([
        'store_id' => $foreign->id,
        'name_ar' => 'منتج منافس',
        'price' => 10,
        'stock' => 10,
    ]);

    $this->postJson("/api/vendor/products/{$foreignProduct->id}/stock-adjustments", ['delta' => 5])
        ->assertNotFound();

    $this->assertDatabaseHas('products', ['id' => $foreignProduct->id, 'stock' => 10]);
    $this->assertDatabaseCount('stock_adjustments', 0);
});
