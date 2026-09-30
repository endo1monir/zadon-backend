<?php

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function orderUser(): User
{
    return User::create(['name' => 'عبدالله', 'phone' => '055'.fake()->unique()->numerify('#######'), 'role' => 'customer']);
}

function makeOrder(User $user, array $attributes = []): Order
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

    $order = Order::create(array_merge([
        'order_number' => 'ZD-'.fake()->unique()->numerify('########'),
        'user_id' => $user->id,
        'store_id' => $store->id,
        'status' => 'new',
        'payment_method' => 'cash',
        'payment_status' => 'pending',
        'subtotal' => 10,
        'vat_amount' => 1.5,
        'delivery_fee' => 0,
        'total' => 11.5,
        'customer_name' => $user->name,
        'customer_phone' => $user->phone,
    ], $attributes));

    $order->items()->create([
        'product_id' => $product->id,
        'product_name_ar' => $product->name_ar,
        'unit_price' => $product->price,
        'quantity' => 1,
    ]);

    return $order;
}

test('order details return the payment method object', function () {
    $user = orderUser();
    $method = PaymentMethod::create(['key' => 'cash', 'name_ar' => 'الدفع عند الاستلام', 'name_en' => 'Cash on delivery', 'sort_order' => 1]);
    $order = makeOrder($user);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.payment_method.id', $method->id)
        ->assertJsonPath('data.order.payment_method.key', 'cash')
        ->assertJsonPath('data.order.payment_method.name', 'Cash on delivery')
        ->assertJsonStructure(['data' => ['order' => ['payment_method' => ['id', 'key', 'name', 'icon']]]]);
});

test('order details return the payment method matching the stored key', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $wallet = PaymentMethod::create(['key' => 'wallet', 'name_ar' => 'محفظة زادون', 'name_en' => 'Zadon wallet', 'sort_order' => 2]);
    $order = makeOrder($user, ['payment_method' => 'wallet']);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.payment_method.id', $wallet->id)
        ->assertJsonPath('data.order.payment_method.key', 'wallet');
});

test('order details expose separate translated names for status and payment status', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $order = makeOrder($user, ['status' => 'out_for_delivery', 'payment_status' => 'paid']);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.status', 'out_for_delivery')
        ->assertJsonPath('data.order.status_name', 'Out for delivery')
        ->assertJsonPath('data.order.payment_status', 'paid')
        ->assertJsonPath('data.order.payment_status_name', 'Paid');

    $this->withHeader('lang', 'ar')
        ->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.status', 'out_for_delivery')
        ->assertJsonPath('data.order.status_name', 'خرج للتوصيل')
        ->assertJsonPath('data.order.payment_status', 'paid')
        ->assertJsonPath('data.order.payment_status_name', 'مدفوع');
});

test('every order status and payment status is translated in english and arabic', function () {
    foreach (Order::STATUSES as $status) {
        $key = "messages.order_status_{$status}";

        $this->assertNotSame($key, trans($key, locale: 'en'), "missing english translation for {$key}");
        $this->assertNotSame($key, trans($key, locale: 'ar'), "missing arabic translation for {$key}");
    }

    foreach (['pending', 'paid', 'failed', 'refunded'] as $status) {
        $key = "messages.payment_status_{$status}";

        $this->assertNotSame($key, trans($key, locale: 'en'), "missing english translation for {$key}");
        $this->assertNotSame($key, trans($key, locale: 'ar'), "missing arabic translation for {$key}");
    }
});

test('order dates are formatted as Y-m-d H:i and unset ones stay null', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $order = makeOrder($user, [
        'accepted_at' => '2026-09-27 08:05:00',
        'prepared_at' => '2026-09-27 09:07:00',
        'ready_at' => '2026-09-27 10:11:00',
        'out_for_delivery_at' => '2026-09-27 11:13:00',
        'delivered_at' => '2026-09-27 12:15:00',
        'cancelled_at' => null,
    ]);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.accepted_at', '2026-09-27 08:05')
        ->assertJsonPath('data.order.prepared_at', '2026-09-27 09:07')
        ->assertJsonPath('data.order.ready_at', '2026-09-27 10:11')
        ->assertJsonPath('data.order.out_for_delivery_at', '2026-09-27 11:13')
        ->assertJsonPath('data.order.delivered_at', '2026-09-27 12:15')
        ->assertJsonPath('data.order.cancelled_at', null)
        ->assertJsonPath('data.order.created_at', $order->created_at->format('Y-m-d H:i'))
        ->assertJsonPath('data.order.updated_at', $order->updated_at->format('Y-m-d H:i'));
});

test('order details report is_reviewd false when the order has no review', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $order = makeOrder($user);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.is_reviewd', false)
        ->assertJsonPath('data.order.review', null);
});

test('order details return the review and report is_reviewd true', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $order = makeOrder($user, ['status' => 'delivered']);

    $review = Review::create([
        'store_id' => $order->store_id,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'customer_name' => $user->name,
        'rating' => 5,
        'comment' => 'خدمة ممتازة',
        'published' => true,
    ]);

    Sanctum::actingAs($user, ['app']);

    $this->getJson("/api/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.order.is_reviewd', true)
        ->assertJsonPath('data.order.review.id', $review->id)
        ->assertJsonPath('data.order.review.rating', 5)
        ->assertJsonPath('data.order.review.comment', 'خدمة ممتازة');
});

test('is_reviewd is reported without an extra query per order in the list', function () {
    $user = orderUser();
    PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);

    $reviewed = makeOrder($user);
    Review::create([
        'store_id' => $reviewed->store_id,
        'user_id' => $user->id,
        'order_id' => $reviewed->id,
        'customer_name' => $user->name,
        'rating' => 4,
    ]);

    makeOrder($user);

    Sanctum::actingAs($user, ['app']);

    $response = $this->getJson('/api/orders')->assertOk();
    $flags = collect($response->json('data.orders'))->pluck('is_reviewd')->all();

    $this->assertSame([true, false], $flags);
});

test('the order list also returns the payment method object and status keys', function () {
    $user = orderUser();
    $method = PaymentMethod::create(['key' => 'cash', 'name_ar' => 'نقداً', 'name_en' => 'Cash', 'sort_order' => 1]);
    $order = makeOrder($user, ['status' => 'preparing']);

    Sanctum::actingAs($user, ['app']);

    $this->getJson('/api/orders')
        ->assertOk()
        ->assertJsonPath('data.orders.0.payment_method.id', $method->id)
        ->assertJsonPath('data.orders.0.status_name', 'Preparing')
        ->assertJsonPath('data.orders.0.payment_status_name', 'Pending');
});
