<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function () {
    $this->get(route('admin.admins.index'))->assertRedirect(route('admin.login'));
});

it('forbids authenticated non admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get(route('admin.admins.index'))
        ->assertForbidden();
});

describe('admin management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists only admins', function () {
        User::factory()->create(['name' => 'Sarah', 'role' => 'admin']);
        User::factory()->create(['name' => 'Regular Customer', 'role' => 'customer']);

        $this->get(route('admin.admins.index'))
            ->assertOk()
            ->assertSee('Sarah')
            ->assertDontSee('Regular Customer');
    });

    it('searches admins', function () {
        User::factory()->create(['name' => 'Sarah', 'role' => 'admin']);
        User::factory()->create(['name' => 'Khaled', 'role' => 'admin']);

        $this->get(route('admin.admins.index', ['search' => 'Khaled']))
            ->assertOk()
            ->assertSee('Khaled')
            ->assertDontSee('Sarah');
    });

    it('filters admins by status', function () {
        User::factory()->create(['name' => 'Active Admin', 'role' => 'admin', 'is_active' => true]);
        User::factory()->create(['name' => 'Inactive Admin', 'role' => 'admin', 'is_active' => false]);

        $this->get(route('admin.admins.index', ['is_active' => '0']))
            ->assertOk()
            ->assertSee('Inactive Admin')
            ->assertDontSee('Active Admin');
    });

    it('renders the create form', function () {
        $this->get(route('admin.admins.create'))->assertOk();
    });

    it('creates an admin with the admin role', function () {
        $city = City::factory()->create();

        $this->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'new.admin@zadon.sa',
            'phone' => '0509999999',
            'password' => 'password123',
            'city_id' => $city->id,
            'is_active' => '1',
        ])->assertRedirect(route('admin.admins.index'));

        $admin = User::where('email', 'new.admin@zadon.sa')->firstOrFail();

        expect($admin)
            ->role->toBe('admin')
            ->is_active->toBeTrue()
            ->name->toBe('New Admin')
            ->city_id->toBe($city->id)
            ->and(Hash::check('password123', $admin->password))->toBeTrue();
    });

    it('requires the name, email and password', function () {
        $this->post(route('admin.admins.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password']);
    });

    it('rejects a duplicate email', function () {
        User::factory()->create(['email' => 'taken@zadon.sa']);

        $this->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'taken@zadon.sa',
            'password' => 'password123',
        ])->assertSessionHasErrors('email');
    });

    it('rejects a duplicate phone', function () {
        User::factory()->create(['phone' => '0501234567']);

        $this->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'unique@zadon.sa',
            'phone' => '0501234567',
            'password' => 'password123',
        ])->assertSessionHasErrors('phone');
    });

    it('rejects a short password', function () {
        $this->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'short@zadon.sa',
            'password' => 'short',
        ])->assertSessionHasErrors('password');
    });

    it('uploads an avatar for a new admin', function () {
        Storage::fake('public');

        $this->post(route('admin.admins.store'), [
            'name' => 'New Admin',
            'email' => 'avatar@zadon.sa',
            'password' => 'password123',
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.admins.index'));

        $admin = User::where('email', 'avatar@zadon.sa')->firstOrFail();

        expect($admin->avatar)->not->toBeNull();

        Storage::disk('public')->assertExists($admin->avatar);
    });

    it('updates an admin', function () {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Old Name']);

        $this->put(route('admin.admins.update', $admin), [
            'name' => 'New Name',
            'email' => $admin->email,
            'is_active' => '1',
        ])->assertRedirect(route('admin.admins.index'));

        expect($admin->refresh())
            ->name->toBe('New Name')
            ->role->toBe('admin');
    });

    it('keeps the current password when the field is left blank', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $hash = $admin->password;

        $this->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
        ])->assertRedirect(route('admin.admins.index'));

        expect($admin->refresh()->password)->toBe($hash);
    });

    it('changes the password when a new one is provided', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => 'new-password-123',
        ])->assertRedirect(route('admin.admins.index'));

        expect(Hash::check('new-password-123', $admin->refresh()->password))->toBeTrue();
    });

    it('ignores an email already used by another admin', function () {
        User::factory()->create(['email' => 'first@zadon.sa', 'role' => 'admin']);
        $admin = User::factory()->create(['email' => 'second@zadon.sa', 'role' => 'admin']);

        $this->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => 'first@zadon.sa',
        ])->assertSessionHasErrors('email');
    });

    it('toggles an admin status', function () {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->patch(route('admin.admins.toggle', $admin))->assertRedirect();

        expect($admin->refresh()->is_active)->toBeFalse();
    });

    it('refuses to deactivate the signed in admin', function () {
        $admin = auth()->user();

        $this->patch(route('admin.admins.toggle', $admin))->assertSessionHas('error');

        expect($admin->refresh()->is_active)->toBeTrue();
    });

    it('refuses to deactivate the signed in admin from the edit form', function () {
        $admin = auth()->user();

        $this->put(route('admin.admins.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');

        expect($admin->refresh()->is_active)->toBeTrue();
    });

    it('refuses to delete the signed in admin', function () {
        $admin = auth()->user();

        $this->delete(route('admin.admins.destroy', $admin))->assertSessionHas('error');

        expect(User::whereKey($admin->id)->exists())->toBeTrue();
    });

    it('deletes another admin', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->delete(route('admin.admins.destroy', $admin))->assertRedirect();

        expect(User::whereKey($admin->id)->exists())->toBeFalse();
    });

    it('cannot manage a user that is not an admin', function (string $method, string $routeName) {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->{$method}(route($routeName, $customer))->assertNotFound();
    })->with([
        ['get', 'admin.admins.edit'],
        ['put', 'admin.admins.update'],
        ['patch', 'admin.admins.toggle'],
        ['delete', 'admin.admins.destroy'],
    ]);
});
