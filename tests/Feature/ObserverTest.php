<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function prime(string ...$keys): void
{
    foreach ($keys as $key) {
        Cache::put($key, 'primed', 600);
    }
}

function makeStore(User $owner): Store
{
    return Store::create([
        'owner_id' => $owner->id,
        'name_ar' => 'متجر',
        'address' => 'الرياض',
    ]);
}

function makeCustomer(): User
{
    return User::create(['name' => 'عميل', 'phone' => '055'.fake()->unique()->numerify('#######'), 'role' => 'customer']);
}

test('creating a banner clears the banners cache', function () {
    prime('home:banners');

    Banner::create(['image' => 'banners/a.jpg']);

    expect(Cache::has('home:banners'))->toBeFalse();
});

test('updating a banner clears the banners cache', function () {
    $banner = Banner::create(['image' => 'banners/a.jpg']);

    prime('home:banners');
    $banner->update(['image' => 'banners/b.jpg']);

    expect(Cache::has('home:banners'))->toBeFalse();
});

test('deleting a banner clears the banners cache', function () {
    $banner = Banner::create(['image' => 'banners/a.jpg']);

    prime('home:banners');
    $banner->delete();

    expect(Cache::has('home:banners'))->toBeFalse();
});

test('saving a category clears the categories cache', function () {
    prime('home:categories');

    Category::create(['type' => 'store', 'name_ar' => 'بقالة', 'name_en' => 'Grocery', 'slug' => 'grocery', 'sort_order' => 1]);

    expect(Cache::has('home:categories'))->toBeFalse();
});

test('deleting a category clears the categories cache', function () {
    $category = Category::create(['type' => 'store', 'name_ar' => 'بقالة', 'name_en' => 'Grocery', 'slug' => 'grocery', 'sort_order' => 1]);

    prime('home:categories');
    $category->delete();

    expect(Cache::has('home:categories'))->toBeFalse();
});

test('saving a store clears the featured stores cache', function () {
    $owner = makeCustomer();

    prime('home:featured_stores');
    makeStore($owner);

    expect(Cache::has('home:featured_stores'))->toBeFalse();
});

test('deleting a store clears the featured stores cache', function () {
    $store = makeStore(makeCustomer());

    prime('home:featured_stores');
    $store->delete();

    expect(Cache::has('home:featured_stores'))->toBeFalse();
});

test('saving a review clears the featured stores cache', function () {
    $store = makeStore(makeCustomer());
    $customer = makeCustomer();

    prime('home:featured_stores');
    Review::create([
        'store_id' => $store->id,
        'user_id' => $customer->id,
        'customer_name' => $customer->name,
        'rating' => 5,
    ]);

    expect(Cache::has('home:featured_stores'))->toBeFalse();
});

test('deleting a review clears the featured stores cache', function () {
    $store = makeStore(makeCustomer());
    $customer = makeCustomer();

    $review = Review::create([
        'store_id' => $store->id,
        'user_id' => $customer->id,
        'customer_name' => $customer->name,
        'rating' => 5,
    ]);

    prime('home:featured_stores');
    $review->delete();

    expect(Cache::has('home:featured_stores'))->toBeFalse();
});

test('submitting a review clears the featured stores cache', function () {
    $user = makeCustomer();
    $store = makeStore($user);
    $order = Order::create([
        'order_number' => 'ZD-'.fake()->unique()->numerify('########'),
        'user_id' => $user->id,
        'store_id' => $store->id,
        'status' => 'delivered',
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'subtotal' => 10,
        'vat_amount' => 1.5,
        'delivery_fee' => 0,
        'total' => 11.5,
        'customer_name' => $user->name,
        'customer_phone' => $user->phone,
    ]);

    Sanctum::actingAs($user, ['app']);

    prime('home:featured_stores');

    $this->postJson("/api/orders/{$order->id}/review", ['rating' => 5, 'comment' => 'great'])
        ->assertStatus(201);

    expect(Cache::has('home:featured_stores'))->toBeFalse()
        ->and((float) $store->fresh()->rating)->toEqual(5.0);
});

test('a banner change does not clear the other home caches', function () {
    prime('home:categories', 'home:featured_stores');

    Banner::create(['image' => 'banners/a.jpg']);

    expect(Cache::has('home:categories'))->toBeTrue()
        ->and(Cache::has('home:featured_stores'))->toBeTrue();
});
