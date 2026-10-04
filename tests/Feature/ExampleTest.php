<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects the base url to the admin login', function () {
    $this->get('/')->assertRedirect(route('admin.login'));
});

it('redirects the base url to the dashboard for admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get('/')
        ->assertRedirect(route('admin.dashboard'));
});
