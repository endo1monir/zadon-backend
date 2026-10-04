<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = actingAsAdmin();
});

it('requires authentication to switch the locale', function (): void {
    auth()->logout();

    $this->post(route('admin.locale.update'), ['locale' => 'ar'])
        ->assertRedirect(route('admin.login'));
});

it('rejects non admin users', function (): void {
    $this->actingAs(User::factory()->create(['role' => 'customer']));

    $this->post(route('admin.locale.update'), ['locale' => 'ar'])->assertForbidden();
});

it('rejects an unsupported locale', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'fr'])->assertSessionHasErrors('locale');

    $this->assertNotSame('fr', session('locale'));
});

it('switches the dashboard to arabic and persists it in the session', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar'])
        ->assertRedirect()
        ->assertSessionHas('locale', 'ar');

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('dir="rtl"', escape: false);
});

it('renders english by default', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('dir="ltr"', escape: false)
        ->assertSee('Dashboard');
});

it('keeps the arabic locale across requests', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar']);

    $this->get(route('admin.users.index'))->assertOk()->assertSee('dir="rtl"', escape: false);
});

it('offers both languages in the navbar switcher', function (): void {
    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('English')
        ->assertSee('العربية')
        ->assertSee(route('admin.locale.update'), escape: false);
});

it('switches back to english', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar']);
    $this->post(route('admin.locale.update'), ['locale' => 'en'])
        ->assertSessionHas('locale', 'en');

    $this->get(route('admin.dashboard'))->assertOk()->assertSee('dir="ltr"', escape: false);
});

it('translates the sidebar items', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar']);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('لوحة التحكم')
        ->assertSee('العملاء')
        ->assertSee('الطلبات');
});

it('resolves the api locale from the lang header', function (): void {
    $this->withHeader('lang', 'ar')
        ->getJson('/api/socials')
        ->assertOk();
});

it('translates page titles and table headers', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar']);

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('العملاء')
        ->assertSee('رقم الجوال')
        ->assertSee('العناوين')
        ->assertSee('الإجراءات')
        ->assertDontSee('admin.pages.users.title');
});

it('translates table headers on the orders page', function (): void {
    $this->post(route('admin.locale.update'), ['locale' => 'ar']);

    $this->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('الطلبات')
        ->assertSee('العميل')
        ->assertSee('الإجمالي')
        ->assertDontSee('admin.th.');
});

it('interpolates dynamic page titles', function (): void {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->post(route('admin.locale.update'), ['locale' => 'ar']);

    $this->get(route('admin.users.addresses', $user))
        ->assertOk()
        ->assertSee('العناوين · Jane Doe');
});

it('keeps english page titles and table headers untranslated', function (): void {
    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Customers')
        ->assertSee('Phone')
        ->assertSee('Addresses')
        ->assertDontSee('admin.th.');
});

it('keeps the english and arabic admin translations in sync', function (): void {
    $flatten = function (array $lines, string $prefix = '') use (&$flatten): array {
        $keys = [];

        foreach ($lines as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            $keys = array_merge($keys, is_array($value) ? $flatten($value, $path) : [$path]);
        }

        return $keys;
    };

    $english = $flatten(require lang_path('en/admin.php'));
    $arabic = $flatten(require lang_path('ar/admin.php'));

    expect($arabic)->toEqual($english)
        ->and($english)->not->toBeEmpty();
});
