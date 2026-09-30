<?php

use App\Models\Cart;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function checkoutUser(): User
{
    return User::create(['name' => 'عبدالله', 'phone' => '055'.fake()->unique()->numerify('#######'), 'role' => 'customer']);
}

function checkoutCart(User $user): Cart
{
    $store = Store::create([
        'owner_id' => $user->id,
        'name_ar' => 'صيدلية',
        'address' => 'الرياض',
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'name_ar' => 'بانادول',
        'price' => 10,
        'stock' => 50,
    ]);

    $cart = Cart::create(['user_id' => $user->id, 'store_id' => $store->id, 'status' => 'active']);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]);

    return $cart;
}

function seedKey(string $key, bool $isActive = true): PaymentMethod
{
    return PaymentMethod::create([
        'key' => $key,
        'name_ar' => $key,
        'name_en' => $key,
        'is_active' => $isActive,
        'sort_order' => 1,
    ]);
}

test('checkout accepts any active payment method key', function () {
    $user = checkoutUser();
    checkoutCart($user);
    seedKey('card');

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/checkout', ['payment_method' => 'card'])
        ->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.order.payment_method.key', 'card');
});

test('checkout rejects a payment method key that is not in the table', function () {
    $user = checkoutUser();
    checkoutCart($user);
    seedKey('card');

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/checkout', ['payment_method' => 'crypto'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('payment_method');
});

test('checkout rejects an inactive payment method key', function () {
    $user = checkoutUser();
    checkoutCart($user);
    seedKey('cash', isActive: false);

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/checkout', ['payment_method' => 'cash'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('payment_method');
});

test('checkout accepts a key added after the hardcoded list was removed', function () {
    $user = checkoutUser();
    checkoutCart($user);
    seedKey('stc_pay');

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/checkout', ['payment_method' => 'stc_pay'])
        ->assertStatus(201)
        ->assertJsonPath('data.order.payment_method.key', 'stc_pay');
});

test('checkout requires a payment method', function () {
    $user = checkoutUser();
    checkoutCart($user);
    seedKey('card');

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/checkout', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('payment_method');
});
