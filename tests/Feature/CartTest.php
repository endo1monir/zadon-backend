<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function cartUser(string $name = 'عبدالله'): User
{
    return User::create(['name' => $name, 'phone' => '055'.fake()->unique()->numerify('#######'), 'role' => 'customer']);
}

function seedCard(): PaymentMethod
{
    return PaymentMethod::create(['key' => 'card', 'name_ar' => 'بطاقة', 'name_en' => 'Card', 'sort_order' => 1]);
}

function cartProduct(User $owner): Product
{
    $store = Store::create([
        'owner_id' => $owner->id,
        'name_ar' => 'صيدلية',
        'address_ar' => 'الرياض',
        'city' => 'Riyadh',
    ]);

    return Product::create([
        'store_id' => $store->id,
        'name_ar' => 'بانادول',
        'price' => 10,
        'stock' => 50,
    ]);
}

function cartWithItem(User $user, Product $product): Cart
{
    $cart = Cart::create(['user_id' => $user->id, 'store_id' => $product->store_id, 'status' => 'active']);

    $cart->items()->create(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => $product->price]);

    return $cart;
}

test('adding a cart item returns the payment methods and default address', function () {
    $user = cartUser();
    $product = cartProduct($user);

    $default = Address::create(['user_id' => $user->id, 'title' => 'home', 'full_address' => 'Default address', 'is_default' => true]);
    Address::create(['user_id' => $user->id, 'title' => 'work', 'full_address' => 'Work address', 'is_default' => false]);
    $method = seedCard();

    Sanctum::actingAs($user, ['app']);

    $this->postJson('/api/cart/items', [
        'store_id' => $product->store_id,
        'product_id' => $product->id,
        'quantity' => 2,
    ])
        ->assertOk()
        ->assertJsonStructure(['data' => ['cart', 'payment_methods', 'default_address']])
        ->assertJsonPath('data.default_address.id', $default->id)
        ->assertJsonPath('data.payment_methods.0.id', $method->id)
        ->assertJsonPath('data.payment_methods.0.key', 'card');
});

test('the newest address is returned when no address is default', function () {
    $user = cartUser();
    $product = cartProduct($user);

    Address::create(['user_id' => $user->id, 'title' => 'home', 'full_address' => 'Oldest address', 'is_default' => false]);
    $newest = Address::create(['user_id' => $user->id, 'title' => 'work', 'full_address' => 'Newest address', 'is_default' => false]);

    cartWithItem($user, $product);
    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonPath('data.default_address.id', $newest->id);
});

test('the default address wins over a newer non default address', function () {
    $user = cartUser();
    $product = cartProduct($user);

    $default = Address::create(['user_id' => $user->id, 'title' => 'home', 'full_address' => 'Default address', 'is_default' => true]);
    Address::create(['user_id' => $user->id, 'title' => 'work', 'full_address' => 'Newer address', 'is_default' => false]);

    cartWithItem($user, $product);
    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonPath('data.default_address.id', $default->id);
});

test('the default address is null when the user has no addresses', function () {
    $user = cartUser();
    $product = cartProduct($user);

    cartWithItem($user, $product);
    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonPath('data.default_address', null)
        ->assertJsonPath('data.payment_methods', []);
});

test('inactive payment methods are excluded from the cart payload', function () {
    $user = cartUser();
    $product = cartProduct($user);

    $active = seedCard();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'is_active' => false, 'sort_order' => 2]);

    cartWithItem($user, $product);
    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonCount(1, 'data.payment_methods')
        ->assertJsonPath('data.payment_methods.0.id', $active->id);
});

test('the cart payload is returned for a user without a cart', function () {
    $user = cartUser();
    $default = Address::create(['user_id' => $user->id, 'title' => 'home', 'full_address' => 'Default address', 'is_default' => true]);

    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonPath('data.cart.items', [])
        ->assertJsonPath('data.cart.total', 0)
        ->assertJsonPath('data.default_address.id', $default->id);
});

test('updating and removing cart items also return the payment methods and default address', function () {
    $user = cartUser();
    $product = cartProduct($user);

    $default = Address::create(['user_id' => $user->id, 'title' => 'home', 'full_address' => 'Default address', 'is_default' => true]);
    $method = seedCard();

    $cart = cartWithItem($user, $product);
    $cartItem = CartItem::where('cart_id', $cart->id)->firstOrFail();

    Sanctum::actingAs($user, ['app']);

    $this->patchJson("/api/cart/items/{$cartItem->id}", ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('data.default_address.id', $default->id)
        ->assertJsonPath('data.payment_methods.0.id', $method->id);

    $this->deleteJson("/api/cart/items/{$cartItem->id}")
        ->assertOk()
        ->assertJsonPath('data.default_address.id', $default->id)
        ->assertJsonPath('data.payment_methods.0.id', $method->id);
});

test('the cart payload only exposes the current user address', function () {
    $user = cartUser('عبدالله');
    $other = cartUser('فاطمة');
    $product = cartProduct($user);

    Address::create(['user_id' => $other->id, 'title' => 'home', 'full_address' => 'Other address', 'is_default' => true]);
    $method = seedCard();

    cartWithItem($user, $product);
    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonPath('data.default_address', null)
        ->assertJsonPath('data.payment_methods.0.id', $method->id);
});
