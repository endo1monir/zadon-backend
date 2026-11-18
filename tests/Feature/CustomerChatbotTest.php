<?php

use App\Models\Address;
use App\Models\Cart;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/**
 * Logged-in chatbot tools. Google's HTTP API is faked so the real Gemini
 * handler runs the tools, which call the real customer API endpoints.
 */
beforeEach(function () {
    config([
        'ai.provider' => 'gemini',
        'ai.model' => 'gemini-flash-lite-latest',
        'prism.providers.gemini.api_key' => 'test-key',
    ]);

    PaymentMethod::factory()->create(['key' => 'cash']);
    PaymentMethod::factory()->create(['key' => 'wallet']);
    PaymentMethod::factory()->create(['key' => 'card']);

    $this->customer = User::factory()->create(['role' => 'customer']);
    $this->address = Address::factory()->create(['user_id' => $this->customer->id, 'full_address' => 'الرياض، حي النخيل', 'is_default' => true]);

    $this->store = Store::factory()->create(['delivery_fee' => 10]);
    $this->honey = Product::factory()->create(['store_id' => $this->store->id, 'name_en' => 'Sidr Honey', 'price' => 100, 'stock' => 5]);
});

/**
 * Put one product in the customer's active cart.
 */
function cartWith(User $user, Product $product, int $quantity = 1): Cart
{
    $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active'], ['store_id' => $product->store_id]);
    $cart->items()->create(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $product->price]);

    return $cart;
}

/**
 * @param  array<int, array<string, mixed>>  $responses
 */
function fakeGemini(array $responses): void
{
    $sequence = Http::sequence();

    foreach ($responses as $response) {
        $sequence->push($response);
    }

    Http::fake(['generativelanguage.googleapis.com/*' => $sequence]);
}

test('guests only get the catalog tools and are told to log in', function () {
    fakeGemini([geminiText('Please log in to see your orders.')]);

    $this->postJson('/api/chat', ['message' => 'Where is my last order?'])->assertOk();

    $request = json_decode(geminiRequestBody(0), true);
    $toolNames = collect($request['tools'][0]['function_declarations'])->pluck('name')->all();

    expect($toolNames)->toBe(['list_offers', 'search_products', 'list_categories'])
        ->and(geminiRequestBody(0))->toContain('NOT logged in');
});

test('a logged-in customer gets every action tool', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([geminiText('Hi')]);

    $this->postJson('/api/chat', ['message' => 'Hi'])->assertOk();

    $request = json_decode(geminiRequestBody(0), true);
    $toolNames = collect($request['tools'][0]['function_declarations'])->pluck('name')->all();

    expect($toolNames)->toContain(
        'get_my_orders', 'get_cart', 'add_to_cart', 'update_cart_quantity', 'remove_from_cart', 'clear_cart',
        'get_profile', 'update_profile', 'get_addresses', 'add_address', 'set_default_address',
        'get_wallet', 'get_notifications', 'contact_support', 'review_order',
        'place_order', 'delete_address', 'confirm_action', 'cancel_action',
    );
});

test('a customer asking for their last order gets only their own latest order', function () {
    Order::factory()->create(['user_id' => $this->customer->id, 'store_id' => $this->store->id, 'order_number' => 'ZD-OLD']);
    $latest = Order::factory()->create(['user_id' => $this->customer->id, 'store_id' => $this->store->id, 'order_number' => 'ZD-LATEST', 'status' => 'preparing']);
    Order::factory()->create(['store_id' => $this->store->id, 'order_number' => 'ZD-SOMEONE-ELSE']);

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('get_my_orders', ['limit' => 1]), geminiText('Your last order is being prepared.')]);

    $this->postJson('/api/chat', ['message' => 'فين آخر طلب ليا؟'])
        ->assertOk()
        ->assertJsonCount(1, 'data.orders')
        ->assertJsonPath('data.orders.0.id', $latest->id);

    expect(geminiRequestBody(1))->toContain('ZD-LATEST')->not->toContain('ZD-OLD')->not->toContain('ZD-SOMEONE-ELSE');
});

test('the bot adds a product to the cart through the cart API', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('add_to_cart', ['product_id' => $this->honey->id, 'quantity' => 2]), geminiText('Added.')]);

    $this->postJson('/api/chat', ['message' => 'ضيف 2 عسل سدر'])
        ->assertOk()
        ->assertJsonPath('data.products.0.id', $this->honey->id);

    $cart = Cart::where('user_id', $this->customer->id)->where('status', 'active')->first();

    expect($cart->items()->where('product_id', $this->honey->id)->value('quantity'))->toBe(2);
});

test('adding more than the stock is refused with the API message', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('add_to_cart', ['product_id' => $this->honey->id, 'quantity' => 50]), geminiText('Not enough stock.')]);

    $this->withHeader('lang', 'en')->postJson('/api/chat', ['message' => 'Add 50 honey'])->assertOk();

    expect(Cart::where('user_id', $this->customer->id)->exists())->toBeFalse()
        ->and(geminiRequestBody(1))->toMatch('/done\W+false/')->toContain('Not enough stock');
});

test('the bot does not empty a cart from another store without the customer agreeing', function () {
    $otherStoreProduct = Product::factory()->create(['price' => 20, 'stock' => 5]);
    $existingCart = cartWith($this->customer, $otherStoreProduct);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('add_to_cart', ['product_id' => $this->honey->id]),
        geminiText('Your cart has another store. Replace it?'),
        geminiToolCall('add_to_cart', ['product_id' => $this->honey->id, 'replace_cart' => 'yes']),
        geminiText('Done.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Add Sidr honey'])->assertOk();

    expect($existingCart->fresh()->status)->toBe('active')
        ->and(geminiRequestBody(1))->toContain('needs_customer_confirmation');

    $this->postJson('/api/chat', ['message' => 'Yes, replace it'])->assertOk();

    expect($existingCart->fresh()->status)->toBe('abandoned');
});

test('the bot changes a quantity and removes a product', function () {
    $vitamin = Product::factory()->create(['store_id' => $this->store->id, 'price' => 30, 'stock' => 10]);
    $cart = cartWith($this->customer, $this->honey);
    cartWith($this->customer, $vitamin);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('update_cart_quantity', ['product_id' => $this->honey->id, 'quantity' => 3]),
        geminiToolCall('remove_from_cart', ['product_id' => $vitamin->id]),
        geminiText('Updated.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Make the honey 3 and remove the vitamin'])->assertOk();

    expect($cart->items()->pluck('quantity', 'product_id')->all())->toBe([$this->honey->id => 3]);
});

test('asking to delete the cart deletes the cart and its items', function () {
    $vitamin = Product::factory()->create(['store_id' => $this->store->id, 'price' => 30, 'stock' => 10]);
    $cart = cartWith($this->customer, $this->honey);
    cartWith($this->customer, $vitamin);

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('clear_cart'), geminiText('Your cart is empty now.')]);

    $this->postJson('/api/chat', ['message' => 'احذفلي الكارت'])->assertOk();

    $this->assertModelMissing($cart);
    $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);

    expect(geminiRequestBody(1))->toContain('The cart was deleted.');
});

test('an order is never placed in the same reply that prepared it', function () {
    cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('place_order', ['payment_method' => 'cash']),
        geminiToolCall('confirm_action'),
        geminiText('Placed!'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Order my cart with cash'])->assertOk();

    expect(Order::count())->toBe(0)
        ->and(geminiRequestBody(2))->toContain('has not confirmed yet');
});

test('a prepared order is placed through checkout after the customer confirms', function () {
    cartWith($this->customer, $this->honey, quantity: 2);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('place_order', ['payment_method' => 'cash']),
        geminiText('Total is 240 SAR. Confirm?'),
        geminiToolCall('confirm_action'),
        geminiText('Your order is placed.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'اعمل أوردر بالكاش'])->assertOk();

    expect(Order::count())->toBe(0)
        ->and(geminiRequestBody(1))->toContain('delivery_address')->toMatch('/total\W+240/');

    $response = $this->postJson('/api/chat', ['message' => 'تمام أكد'])->assertOk();

    $order = Order::sole();

    expect((float) $order->total)->toBe(240.0)
        ->and($order->user_id)->toBe($this->customer->id)
        ->and($order->payment_method)->toBe('cash')
        ->and($order->delivery_address)->toBe('الرياض، حي النخيل')
        ->and($this->honey->fresh()->stock)->toBe(3);

    $response->assertJsonPath('data.orders.0.order_number', $order->order_number);
});

test('an order is not placed when the cart changed after the summary', function () {
    $cart = cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('place_order', ['payment_method' => 'cash']),
        geminiText('Confirm?'),
        geminiToolCall('confirm_action'),
        geminiText('The cart changed.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Order with cash'])->assertOk();

    $cart->items()->first()->update(['quantity' => 4]);

    $this->postJson('/api/chat', ['message' => 'Yes'])->assertOk();

    expect(Order::count())->toBe(0)
        ->and(geminiRequestBody(3))->toContain('cart changed');
});

test('a cancelled order is not placed even if confirmed later', function () {
    cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('place_order', ['payment_method' => 'cash']),
        geminiText('Confirm?'),
        geminiToolCall('cancel_action'),
        geminiText('Cancelled.'),
        geminiToolCall('confirm_action'),
        geminiText('Nothing to confirm.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Order with cash'])->assertOk();
    $this->postJson('/api/chat', ['message' => 'No, cancel'])->assertOk();
    $this->postJson('/api/chat', ['message' => 'Yes'])->assertOk();

    expect(Order::count())->toBe(0)
        ->and(geminiRequestBody(5))->toContain('nothing waiting for confirmation');
});

test('confirm without a prepared action is refused', function () {
    cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('confirm_action'), geminiText('Let me prepare it first.')]);

    $this->postJson('/api/chat', ['message' => 'Just order it'])->assertOk();

    expect(Order::count())->toBe(0)
        ->and(geminiRequestBody(1))->toContain('nothing waiting for confirmation');
});

test('card payment cannot be used to order in chat', function () {
    cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('place_order', ['payment_method' => 'card']), geminiText('Use the cart screen for card.')]);

    $this->postJson('/api/chat', ['message' => 'Order with my card'])->assertOk();

    expect(geminiRequestBody(1))->toContain('cannot be used in chat');
});

test('an order needs a saved delivery address', function () {
    Address::query()->delete();
    cartWith($this->customer, $this->honey);

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('place_order', ['payment_method' => 'cash']), geminiText('Add an address first.')]);

    $this->postJson('/api/chat', ['message' => 'Order with cash'])->assertOk();

    expect(geminiRequestBody(1))->toContain('no saved delivery address');
});

test('an address is deleted only after the customer confirms', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('delete_address', ['address_id' => $this->address->id]),
        geminiText('Delete this address?'),
        geminiToolCall('confirm_action'),
        geminiText('Deleted.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Delete my home address'])->assertOk();

    expect($this->address->fresh())->not->toBeNull();

    $this->postJson('/api/chat', ['message' => 'Yes'])->assertOk();

    expect($this->address->fresh())->toBeNull();
});

test('the bot cannot touch another customer address', function () {
    $otherAddress = Address::factory()->create();

    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('delete_address', ['address_id' => $otherAddress->id]), geminiText('Not found.')]);

    $this->postJson('/api/chat', ['message' => 'Delete address '.$otherAddress->id])->assertOk();

    expect(geminiRequestBody(1))->toContain('No such address')
        ->and($otherAddress->fresh())->not->toBeNull();
});

test('the bot adds an address and updates the profile', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('add_address', ['full_address' => 'جدة، حي الروضة', 'title' => 'work']),
        geminiToolCall('update_profile', ['name' => 'Hala']),
        geminiText('Done.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Add my work address in Jeddah and change my name to Hala'])->assertOk();

    expect($this->customer->addresses()->where('full_address', 'جدة، حي الروضة')->value('is_default'))->toBeTruthy()
        ->and($this->customer->fresh()->name)->toBe('Hala');
});

test('an invalid email comes back to the model as a validation error', function () {
    Sanctum::actingAs($this->customer);
    fakeGemini([geminiToolCall('update_profile', ['email' => 'not-an-email']), geminiText('That email is not valid.')]);

    $this->postJson('/api/chat', ['message' => 'Change my email to not-an-email'])->assertOk();

    expect(geminiRequestBody(1))->toContain('validation_errors')
        ->and($this->customer->fresh()->email)->not->toBe('not-an-email');
});

test('the bot reviews a delivered order and contacts support', function () {
    $order = Order::factory()->create(['user_id' => $this->customer->id, 'store_id' => $this->store->id, 'order_number' => 'ZD-DONE', 'status' => 'delivered']);

    Sanctum::actingAs($this->customer);
    fakeGemini([
        geminiToolCall('review_order', ['order_number' => 'ZD-DONE', 'rating' => 5, 'comment' => 'ممتاز']),
        geminiToolCall('contact_support', ['title' => 'Thanks', 'message' => 'Great service']),
        geminiText('Thanks for your review.'),
    ]);

    $this->postJson('/api/chat', ['message' => 'Rate my order ZD-DONE 5 stars and thank support'])->assertOk();

    expect(Review::where('order_id', $order->id)->value('rating'))->toBe(5)
        ->and(ContactMessage::where('user_id', $this->customer->id)->value('title'))->toBe('Thanks');
});
