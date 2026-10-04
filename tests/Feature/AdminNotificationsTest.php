<?php

use App\Models\Store;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\DashboardNotification;
use App\Support\NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = actingAsAdmin();
});

/**
 * @return array<string, mixed>
 */
function notificationPayload(array $overrides = []): array
{
    return [
        'title_ar' => 'تنبيه تجريبي',
        'title_en' => 'Test notice',
        'message_ar' => 'رسالة تجريبية',
        'message_en' => 'Test message',
        'type' => 'system',
        ...$overrides,
    ];
}

it('requires authentication', function (): void {
    auth()->logout();

    $this->get(route('admin.notifications.index'))->assertRedirect(route('admin.login'));
});

it('rejects non admin users', function (): void {
    $this->actingAs(User::factory()->create(['role' => 'customer']));

    $this->get(route('admin.notifications.index'))->assertForbidden();
    $this->post(route('admin.notifications.store'), notificationPayload(['audience' => 'all']))->assertForbidden();
});

it('lists the notifications tab from the sidebar', function (): void {
    $this->get(route('admin.notifications.index'))->assertOk()->assertSee('Notifications');
});

it('broadcasts to all clients', function (): void {
    User::factory()->count(2)->create(['role' => 'customer']);
    $vendor = User::factory()->create(['role' => 'vendor']);

    $this->post(route('admin.notifications.store'), notificationPayload(['audience' => 'clients']))
        ->assertSessionHas('success');

    expect(UserNotification::count())->toBe(2)
        ->and(UserNotification::where('user_id', $vendor->id)->count())->toBe(0);
});

it('broadcasts to all vendors and links their managed store', function (): void {
    $vendor = User::factory()->create(['role' => 'vendor']);
    $store = Store::factory()->create(['owner_id' => $vendor->id]);
    User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.notifications.store'), notificationPayload(['audience' => 'vendors']))
        ->assertSessionHas('success');

    $notification = UserNotification::sole();

    expect($notification->user_id)->toBe($vendor->id)
        ->and($notification->store_id)->toBe($store->id);
});

it('broadcasts to everyone', function (): void {
    User::factory()->count(2)->create(['role' => 'customer']);
    User::factory()->count(2)->create(['role' => 'vendor']);

    $this->post(route('admin.notifications.store'), notificationPayload(['audience' => 'all']))
        ->assertSessionHas('success');

    expect(UserNotification::count())->toBe(4);
});

it('persists every notification table field', function (): void {
    $user = User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.notifications.store'), notificationPayload([
        'audience' => 'clients',
        'type' => 'alert',
        'action_label_ar' => 'عرض',
        'action_label_en' => 'View',
    ]))->assertSessionHas('success');

    $notification = UserNotification::sole();

    expect($notification->title_ar)->toBe('تنبيه تجريبي')
        ->and($notification->title_en)->toBe('Test notice')
        ->and($notification->message_ar)->toBe('رسالة تجريبية')
        ->and($notification->message_en)->toBe('Test message')
        ->and($notification->type)->toBe('alert')
        ->and($notification->action_label_ar)->toBe('عرض')
        ->and($notification->action_label_en)->toBe('View')
        ->and($notification->is_read)->toBeFalse()
        ->and($notification->user_id)->toBe($user->id);
});

it('requires an audience for a broadcast', function (): void {
    $this->post(route('admin.notifications.store'), notificationPayload())
        ->assertSessionHasErrors('audience');

    expect(UserNotification::count())->toBe(0);
});

it('validates the notification payload', function (): void {
    $this->post(route('admin.notifications.store'), [
        'audience' => 'martians',
        'title_ar' => '',
        'type' => 'nonsense',
    ])->assertSessionHasErrors(['audience', 'title_ar', 'type']);
});

it('sends to a single customer from the users list', function (): void {
    $user = User::factory()->create(['role' => 'customer']);
    $other = User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.notifications.user', $user), notificationPayload())
        ->assertSessionHas('success');

    expect(UserNotification::sole()->user_id)->toBe($user->id)
        ->and(UserNotification::where('user_id', $other->id)->count())->toBe(0);
});

it('sends to a single vendor from the vendors list', function (): void {
    $vendor = User::factory()->create(['role' => 'vendor']);
    $store = Store::factory()->create(['owner_id' => $vendor->id]);

    $this->post(route('admin.notifications.vendor', $vendor), notificationPayload())
        ->assertSessionHas('success');

    $notification = UserNotification::sole();

    expect($notification->user_id)->toBe($vendor->id)
        ->and($notification->store_id)->toBe($store->id);
});

it('will not send a notification to a vendor through the customer route', function (): void {
    $vendor = User::factory()->create(['role' => 'vendor']);

    $this->post(route('admin.notifications.user', $vendor), notificationPayload())
        ->assertNotFound();

    expect(UserNotification::count())->toBe(0);
});

it('will not send a notification to a customer through the vendor route', function (): void {
    $user = User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.notifications.vendor', $user), notificationPayload())
        ->assertNotFound();

    expect(UserNotification::count())->toBe(0);
});

it('filters the sent history by type', function (): void {
    $match = UserNotification::create([
        'user_id' => User::factory()->create(['role' => 'customer'])->id,
        'title_ar' => 'Order notice',
        'type' => 'order',
    ]);

    UserNotification::create([
        'user_id' => User::factory()->create(['role' => 'customer'])->id,
        'title_ar' => 'Alert notice',
        'type' => 'alert',
    ]);

    $this->get(route('admin.notifications.index', ['type' => 'order']))
        ->assertOk()
        ->assertSee($match->title_ar)
        ->assertDontSee('Alert notice');
});

it('searches the sent history', function (): void {
    $match = UserNotification::create([
        'user_id' => User::factory()->create(['role' => 'customer'])->id,
        'title_ar' => 'Findable notice',
        'title_en' => 'Searchable',
        'type' => 'system',
    ]);

    UserNotification::create([
        'user_id' => User::factory()->create(['role' => 'customer'])->id,
        'title_ar' => 'Unrelated notice',
        'type' => 'system',
    ]);

    $this->get(route('admin.notifications.index', ['search' => 'Findable']))
        ->assertOk()
        ->assertSee($match->title_ar)
        ->assertDontSee('Unrelated notice');
});

it('never fails the send when the fcm driver is missing', function (): void {
    expect(DashboardNotification::fcmAvailable())->toBeFalse();

    $user = User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.notifications.user', $user), notificationPayload())
        ->assertSessionHas('success');

    expect(UserNotification::count())->toBe(1);
});

it('resolves recipients per audience', function (): void {
    $dispatcher = app(NotificationDispatcher::class);

    User::factory()->count(2)->create(['role' => 'customer']);
    User::factory()->create(['role' => 'vendor']);
    User::factory()->create(['role' => 'admin']);

    expect($dispatcher->recipients('clients')->count())->toBe(2)
        ->and($dispatcher->recipients('vendors')->count())->toBe(1)
        ->and($dispatcher->recipients('all')->count())->toBe(3);
});

it('exposes the notify action on the users and vendors lists', function (): void {
    User::factory()->create(['role' => 'customer']);
    User::factory()->create(['role' => 'vendor']);

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee(route('admin.notifications.user', User::customer()->first()), escape: false);

    $this->get(route('admin.vendors.index'))
        ->assertOk()
        ->assertSee(route('admin.notifications.vendor', User::vendor()->first()), escape: false);
});
