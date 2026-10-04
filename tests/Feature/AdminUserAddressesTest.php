<?php

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = actingAsAdmin();
});

it('requires authentication', function (): void {
    auth()->logout();

    $this->get(route('admin.users.addresses', User::factory()->create(['role' => 'customer'])))
        ->assertRedirect(route('admin.login'));
});

it('rejects non admin users', function (): void {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs(User::factory()->create(['role' => 'vendor']));

    $this->get(route('admin.users.addresses', $user))->assertForbidden();
});

it('shows the addresses count and link in the users list', function (): void {
    $user = User::factory()->create(['role' => 'customer']);
    Address::factory()->count(3)->create(['user_id' => $user->id]);

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee(route('admin.users.addresses', $user), escape: false)
        ->assertSeeInOrder(['Addresses']);
});

it('counts zero addresses for a customer without any', function (): void {
    User::factory()->create(['role' => 'customer']);

    $this->get(route('admin.users.index'))->assertOk();
});

it('lists the addresses of a customer', function (): void {
    $user = User::factory()->create(['role' => 'customer']);
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'title' => 'work',
        'full_address' => 'King Fahd Road, Riyadh',
        'is_default' => true,
    ]);

    $this->get(route('admin.users.addresses', $user))
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee('Work')
        ->assertSee('King Fahd Road, Riyadh')
        ->assertSee('Default');
});

it('puts the default address first', function (): void {
    $user = User::factory()->create(['role' => 'customer']);

    Address::factory()->create([
        'user_id' => $user->id,
        'full_address' => 'Older Place',
        'is_default' => false,
    ]);

    Address::factory()->create([
        'user_id' => $user->id,
        'full_address' => 'Default Place',
        'is_default' => true,
    ]);

    $this->get(route('admin.users.addresses', $user))
        ->assertOk()
        ->assertSeeInOrder(['Default Place', 'Older Place']);
});

it('renders an empty state when the customer has no addresses', function (): void {
    $user = User::factory()->create(['role' => 'customer']);

    $this->get(route('admin.users.addresses', $user))
        ->assertOk()
        ->assertSee('No addresses yet');
});

it('never shows another customer addresses', function (): void {
    $user = User::factory()->create(['role' => 'customer']);
    $other = User::factory()->create(['role' => 'customer']);

    Address::factory()->create([
        'user_id' => $other->id,
        'full_address' => 'Private Address',
    ]);

    $this->get(route('admin.users.addresses', $user))
        ->assertOk()
        ->assertDontSee('Private Address');
});

it('will not open addresses for a vendor', function (): void {
    $vendor = User::factory()->create(['role' => 'vendor']);

    $this->get(route('admin.users.addresses', $vendor))->assertNotFound();
});
