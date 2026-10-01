<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin',
        'phone' => '0555000001',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
        'role' => 'admin',
    ]);

    $this->customer = User::create([
        'name' => 'Customer',
        'phone' => '0555000002',
        'email' => 'customer@example.com',
        'password' => Hash::make('password123'),
        'role' => 'customer',
    ]);
});

it('renders the admin login page for guests', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertViewIs('admin.auth.login');
});

it('authenticates an admin and redirects to the dashboard', function () {
    $response = $this->post(route('admin.login.store'), [
        'email' => 'admin@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($this->admin);
});

it('rejects invalid credentials', function () {
    $this->post(route('admin.login.store'), [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('does not sign in a non-admin user', function () {
    $this->post(route('admin.login.store'), [
        'email' => 'customer@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('requires the email field', function () {
    $this->post(route('admin.login.store'), ['password' => 'password123'])
        ->assertSessionHasErrors('email');
});

it('redirects guests away from the dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

it('forbids non-admin users from the dashboard', function () {
    $this->actingAs($this->customer)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('renders the dashboard for admins', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewIs('admin.dashboard.index');
});

it('logs an admin out', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});
