<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = actingAsAdmin();
});

it('requires authentication', function (): void {
    auth()->logout();

    $this->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));
});

it('rejects non admin users', function (): void {
    $this->actingAs(User::factory()->create(['role' => 'customer']));

    $this->get(route('admin.orders.index'))->assertForbidden();
    $this->get(route('admin.orders.show', Order::factory()->create()))->assertForbidden();
});

it('lists orders across all stores', function (): void {
    Order::factory()->count(3)->create();

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('ORD-', escape: false);
});

it('paginates orders', function (): void {
    Order::factory()->count(25)->create();

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('page=2', escape: false);
});

it('searches by order number, customer name and phone', function (): void {
    $match = Order::factory()->create([
        'order_number' => 'ORD-SEARCHABLE',
        'customer_name' => 'Findme Person',
        'customer_phone' => '0500000001',
    ]);
    Order::factory()->create([
        'order_number' => 'ORD-OTHER',
        'customer_name' => 'Nobody Here',
    ]);

    $this->get(route('admin.orders.index', ['search' => 'SEARCHABLE']))
        ->assertOk()
        ->assertSee($match->order_number)
        ->assertDontSee('ORD-OTHER');

    $this->get(route('admin.orders.index', ['search' => 'Findme']))
        ->assertOk()
        ->assertSee($match->order_number)
        ->assertDontSee('ORD-OTHER');

    $this->get(route('admin.orders.index', ['search' => '0500000001']))
        ->assertOk()
        ->assertSee($match->order_number)
        ->assertDontSee('ORD-OTHER');
});

it('filters by status', function (): void {
    $match = Order::factory()->status('preparing')->create();
    Order::factory()->status('delivered')->create();

    $this->get(route('admin.orders.index', ['status' => 'preparing']))
        ->assertOk()
        ->assertSee($match->order_number)
        ->assertDontSee(Order::firstWhere('status', 'delivered')?->order_number);
});

it('filters by payment status and store', function (): void {
    $store = Order::factory()->create(['payment_status' => 'paid'])->store;
    $match = Order::factory()->create([
        'store_id' => $store->id,
        'payment_status' => 'pending',
    ]);

    $this->get(route('admin.orders.index', ['payment_status' => 'paid']))
        ->assertOk()
        ->assertSee($store->orders()->firstWhere('payment_status', 'paid')->order_number)
        ->assertDontSee($match->order_number);

    $this->get(route('admin.orders.index', ['store_id' => $store->id]))
        ->assertOk()
        ->assertSee($store->orders()->first()->order_number);
});

it('shows status counts', function (): void {
    Order::factory()->count(2)->status('preparing')->create();
    Order::factory()->status('cancelled')->create();

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('Preparing', escape: false);
});

it('shows every order detail', function (): void {
    $order = Order::factory()->create([
        'order_number' => 'ORD-DETAILS',
        'customer_name' => 'Detail Customer',
        'customer_phone' => '0501112222',
        'notes' => 'Leave at the door',
        'courier_name' => 'Courier Person',
        'courier_phone' => '0503334444',
        'courier_eta_minutes' => 45,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_name_ar' => 'منتج تجريبي',
        'product_name_en' => 'Sample Product',
        'unit_price' => 100,
        'quantity' => 3,
        'unit' => 'قطعة',
        'packed' => true,
    ]);

    $this->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('ORD-DETAILS')
        ->assertSee('Detail Customer')
        ->assertSee('0501112222')
        ->assertSee('Leave at the door')
        ->assertSee('Courier Person')
        ->assertSee('0503334444')
        ->assertSee('منتج تجريبي')
        ->assertSee('Sample Product')
        ->assertSee('Packed')
        ->assertSee(number_format((float) $order->total, 2))
        ->assertSee($order->store->name_ar);
});

it('renders an order without items or user', function (): void {
    $order = Order::factory()->guest()->create();

    $this->get(route('admin.orders.show', $order))->assertOk()->assertSee('Guest order');
});

it('updates the status and sets the matching timestamp', function (): void {
    $order = Order::factory()->status('new')->create();

    $this->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => 'preparing'])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe('preparing')
        ->and($order->accepted_at)->not->toBeNull();
});

it('allows cancelling a non terminal order', function (): void {
    $order = Order::factory()->status('ready_for_pickup')->create();

    $this->patch(route('admin.orders.status', $order), ['status' => 'cancelled'])
        ->assertSessionHas('success');

    $order->refresh();

    expect($order->status)->toBe('cancelled')
        ->and($order->cancelled_at)->not->toBeNull();
});

it('refuses an invalid transition', function (): void {
    $order = Order::factory()->status('new')->create();

    $this->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => 'delivered'])
        ->assertRedirect(route('admin.orders.show', $order))
        ->assertSessionHas('error');

    expect($order->refresh()->status)->toBe('new');
});

it('refuses transitions out of a terminal status', function (): void {
    $order = Order::factory()->delivered()->create();

    $this->patch(route('admin.orders.status', $order), ['status' => 'preparing'])
        ->assertSessionHas('error');

    expect($order->refresh()->status)->toBe('delivered');
});

it('rejects an unknown status value', function (): void {
    $order = Order::factory()->status('new')->create();

    $this->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), ['status' => 'teleported'])
        ->assertSessionHasErrors('status');

    expect($order->refresh()->status)->toBe('new');
});

it('requires a status value', function (): void {
    $order = Order::factory()->status('new')->create();

    $this->from(route('admin.orders.show', $order))
        ->patch(route('admin.orders.status', $order), [])
        ->assertSessionHasErrors('status');
});

it('notifies the customer about the status change', function (): void {
    $order = Order::factory()->status('new')->create();

    $this->patch(route('admin.orders.status', $order), ['status' => 'preparing']);

    expect($order->user->notifications()->count())->toBe(1);
});

it('does not fail for a guest order', function (): void {
    $order = Order::factory()->guest()->status('new')->create();

    $this->patch(route('admin.orders.status', $order), ['status' => 'preparing'])
        ->assertSessionHas('success');

    expect($order->refresh()->status)->toBe('preparing');
});

it('exposes the orders menu tab', function (): void {
    $this->get(route('admin.orders.index'))->assertOk()->assertSee('Orders');
});
