<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
});

it('forbids authenticated non admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

describe('customer management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists customers', function () {
        User::factory()->create(['name' => 'A Customer', 'role' => 'customer']);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('A Customer');
    });

    it('never lists admins', function () {
        User::factory()->create(['name' => 'A Customer', 'role' => 'customer']);
        User::factory()->create(['name' => 'Another Admin', 'role' => 'admin']);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('A Customer')
            ->assertDontSee('Another Admin');
    });

    it('never lists vendors', function () {
        User::factory()->create(['name' => 'A Customer', 'role' => 'customer']);
        User::factory()->create(['name' => 'A Vendor', 'role' => 'vendor']);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('A Customer')
            ->assertDontSee('A Vendor');
    });

    it('finds no admin or vendor through the search filter', function (string $role, string $email) {
        User::factory()->create(['name' => 'Hidden Account', 'email' => $email, 'role' => $role]);

        $this->get(route('admin.users.index', ['search' => $email]))
            ->assertOk()
            ->assertDontSee('Hidden Account');
    })->with([
        ['admin', 'hidden.admin@zadon.sa'],
        ['vendor', 'hidden.vendor@zadon.sa'],
    ]);

    it('searches customers by name, email and phone', function (string $field) {
        User::factory()->create(['name' => 'Sarah Ahmed', 'email' => 'sarah@zadon.sa', 'phone' => '0511111111', 'role' => 'customer']);
        User::factory()->create(['name' => 'Khaled Omar', 'email' => 'khaled@zadon.sa', 'phone' => '0522222222', 'role' => 'customer']);

        $term = match ($field) {
            'name' => 'Sarah',
            'email' => 'sarah@zadon.sa',
            'phone' => '0511111111',
        };

        $this->get(route('admin.users.index', ['search' => $term]))
            ->assertOk()
            ->assertSee('Sarah Ahmed')
            ->assertDontSee('Khaled Omar');
    })->with(['name', 'email', 'phone']);

    it('filters customers by status', function () {
        User::factory()->create(['name' => 'Active One', 'role' => 'customer', 'is_active' => true]);
        User::factory()->create(['name' => 'Inactive One', 'role' => 'customer', 'is_active' => false]);

        $this->get(route('admin.users.index', ['is_active' => '0']))
            ->assertOk()
            ->assertSee('Inactive One')
            ->assertDontSee('Active One');
    });

    it('renders the create form', function () {
        $this->get(route('admin.users.create'))->assertOk();
    });

    it('creates a customer ready for the app login flow', function () {
        $city = City::factory()->create();

        $this->post(route('admin.users.store'), [
            'name' => 'New Customer',
            'phone' => '0533333333',
            'email' => 'customer@zadon.sa',
            'city_id' => $city->id,
            'is_active' => '1',
            'is_completed' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('phone', '0533333333')->firstOrFail();

        expect($user)
            ->role->toBe('customer')
            ->name->toBe('New Customer')
            ->city_id->toBe($city->id)
            ->is_active->toBeTrue()
            ->is_completed->toBeTrue()
            ->code->toBeNull()
            ->and($user->phone_verified_at)->not->toBeNull()
            ->and($user->email_verified_at)->not->toBeNull();
    });

    it('forces the customer role whatever the request sends', function (string $role) {
        $this->post(route('admin.users.store'), [
            'name' => 'Sneaky Account',
            'phone' => '0512121212',
            'email' => 'sneaky@zadon.sa',
            'role' => $role,
        ])->assertRedirect(route('admin.users.index'));

        expect(User::where('phone', '0512121212')->firstOrFail()->role)->toBe('customer');
    })->with(['admin', 'vendor', 'store_owner']);

    it('requires the phone so the account can sign in', function () {
        $this->post(route('admin.users.store'), [
            'name' => 'No Phone',
            'email' => 'nophone@zadon.sa',
        ])->assertSessionHasErrors('phone');
    });

    it('rejects a duplicate phone', function () {
        User::factory()->create(['phone' => '0544444444']);

        $this->post(route('admin.users.store'), [
            'name' => 'Taken Phone',
            'phone' => '0544444444',
            'email' => 'unique@zadon.sa',
        ])->assertSessionHasErrors('phone');
    });

    it('rejects a duplicate email', function () {
        User::factory()->create(['email' => 'taken@zadon.sa']);

        $this->post(route('admin.users.store'), [
            'name' => 'Taken Email',
            'phone' => '0555555555',
            'email' => 'taken@zadon.sa',
        ])->assertSessionHasErrors('email');
    });

    it('creates a customer without a password because the app signs in with a code', function () {
        $this->post(route('admin.users.store'), [
            'name' => 'No Password',
            'phone' => '0577777777',
            'email' => 'nopassword@zadon.sa',
        ])->assertRedirect(route('admin.users.index'));

        expect(User::where('phone', '0577777777')->firstOrFail()->password)->toBeNull();
    });

    it('uploads an avatar into the avatars directory used by the api', function () {
        Storage::fake('public');

        $this->post(route('admin.users.store'), [
            'name' => 'With Avatar',
            'phone' => '0510101010',
            'email' => 'avatar@zadon.sa',
            'avatar' => UploadedFile::fake()->create('avatar.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'avatar@zadon.sa')->firstOrFail();

        expect($user->avatar)->toStartWith('avatars/');

        Storage::disk('public')->assertExists($user->avatar);
    });

    it('updates a customer', function () {
        $user = User::factory()->create(['name' => 'Old Name', 'role' => 'customer', 'is_completed' => false]);

        $this->put(route('admin.users.update', $user), [
            'name' => 'New Name',
            'phone' => $user->phone ?? '0510202020',
            'email' => $user->email,
            'is_completed' => '1',
        ])->assertRedirect(route('admin.users.index'));

        expect($user->refresh())
            ->name->toBe('New Name')
            ->is_completed->toBeTrue()
            ->role->toBe('customer');
    });

    it('keeps the current password when the field is left blank', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $hash = $user->password;

        $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'phone' => $user->phone ?? '0510303030',
            'email' => $user->email,
            'password' => '',
        ])->assertRedirect(route('admin.users.index'));

        expect($user->refresh()->password)->toBe($hash);
    });

    it('stores the new password in hashed form', function () {
        $user = User::factory()->create(['role' => 'customer']);

        $this->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'phone' => $user->phone ?? '0510303031',
            'email' => $user->email,
            'password' => 'new-password-123',
        ])->assertRedirect(route('admin.users.index'));

        expect(Hash::check('new-password-123', $user->refresh()->password))->toBeTrue();
    });

    it('toggles a customer status', function () {
        $user = User::factory()->create(['role' => 'customer', 'is_active' => true]);

        $this->patch(route('admin.users.toggle', $user))->assertRedirect(route('admin.users.index'));

        expect($user->refresh()->is_active)->toBeFalse();
    });

    it('cannot manage an admin or a vendor through the customers section', function (string $role) {
        $account = User::factory()->create(['role' => $role]);

        $this->get(route('admin.users.edit', $account))->assertNotFound();

        $this->put(route('admin.users.update', $account), [
            'name' => 'Demoted',
            'phone' => $account->phone ?? '0510404040',
            'email' => $account->email,
        ])->assertNotFound();

        $this->patch(route('admin.users.toggle', $account))->assertNotFound();
        $this->delete(route('admin.users.destroy', $account))->assertNotFound();

        expect($account->refresh())->name->not->toBe('Demoted')->role->toBe($role);
    })->with(['admin', 'vendor']);

    it('cannot reach the signed in admin through the customers section', function () {
        $admin = auth()->user();

        $this->get(route('admin.users.edit', $admin))->assertNotFound();
        $this->patch(route('admin.users.toggle', $admin))->assertNotFound();
        $this->delete(route('admin.users.destroy', $admin))->assertNotFound();

        expect(User::whereKey($admin->id)->exists())->toBeTrue();
    });

    it('deletes a customer', function () {
        $user = User::factory()->create(['role' => 'customer']);

        $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));

        expect(User::whereKey($user->id)->exists())->toBeFalse();
    });
});
