<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function () {
    $this->get(route('admin.vendors.index'))->assertRedirect(route('admin.login'));
});

it('forbids authenticated non admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get(route('admin.vendors.index'))
        ->assertForbidden();
});

describe('vendor management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists vendors with their store', function () {
        $vendor = User::factory()->create(['name' => 'Store Owner', 'role' => 'vendor']);
        Store::factory()->create(['owner_id' => $vendor->id, 'name_ar' => 'متجر النور']);

        $this->get(route('admin.vendors.index'))
            ->assertOk()
            ->assertSee('Store Owner')
            ->assertSee('متجر النور');
    });

    it('never lists admins or customers', function () {
        User::factory()->create(['name' => 'The Admin', 'role' => 'admin']);
        User::factory()->create(['name' => 'The Customer', 'role' => 'customer']);

        $this->get(route('admin.vendors.index'))
            ->assertOk()
            ->assertDontSee('The Admin')
            ->assertDontSee('The Customer');
    });

    it('searches vendors by owner and store name', function (string $term, string $expected, string $hidden) {
        $vendor = User::factory()->create(['name' => 'Sarah Ahmed', 'role' => 'vendor']);
        Store::factory()->create(['owner_id' => $vendor->id, 'name_ar' => 'بقالة النور', 'name_en' => 'Al Noor Grocery']);

        User::factory()->create(['name' => 'Khaled Omar', 'role' => 'vendor']);

        $this->get(route('admin.vendors.index', ['search' => $term]))
            ->assertOk()
            ->assertSee($expected)
            ->assertDontSee($hidden);
    })->with([
        ['Sarah', 'Sarah Ahmed', 'Khaled Omar'],
        ['بقالة', 'بقالة النور', 'Khaled Omar'],
        ['Grocery', 'Al Noor Grocery', 'Khaled Omar'],
    ]);

    it('finds no admin or customer through the search filter', function (string $role, string $email) {
        User::factory()->create(['name' => 'Hidden Account', 'email' => $email, 'role' => $role]);

        $this->get(route('admin.vendors.index', ['search' => $email]))
            ->assertOk()
            ->assertDontSee('Hidden Account');
    })->with([
        ['admin', 'hidden.admin@zadon.sa'],
        ['customer', 'hidden.customer@zadon.sa'],
    ]);

    it('filters vendors by status', function () {
        $active = User::factory()->create(['name' => 'Active Vendor', 'role' => 'vendor', 'is_active' => true]);
        $inactive = User::factory()->create(['name' => 'Inactive Vendor', 'role' => 'vendor', 'is_active' => false]);

        $this->get(route('admin.vendors.index', ['is_active' => '0']))
            ->assertOk()
            ->assertSee('Inactive Vendor')
            ->assertDontSee('Active Vendor');
    });

    it('renders the create form', function () {
        $this->get(route('admin.vendors.create'))->assertOk();
    });

    it('creates a vendor account with a store using the register endpoint fields', function () {
        $city = City::factory()->create();
        $category = Category::factory()->create(['type' => 'store']);

        $this->post(route('admin.vendors.store'), [
            'name' => 'New Vendor',
            'phone' => '0533333333',
            'email' => 'vendor@zadon.sa',
            'password' => 'password123',
            'is_active' => '1',
            'store_name_ar' => 'متجر النور',
            'store_name_en' => 'Al Noor Grocery',
            'store_category_id' => $category->id,
            'store_city_id' => $city->id,
            'store_address' => 'الرياض، حي العليا',
            'store_phone' => '0112345678',
            'store_email' => 'store@zadon.sa',
            'store_cr_number' => '1010123456',
            'store_vat_number' => '310121234500003',
            'store_manager_name' => 'Ali Hassan',
            'store_prep_time_min' => 20,
            'store_delivery_fee' => 15.5,
            'store_min_order' => 25,
            'store_delivery_radius_km' => 12,
            'store_is_open_24_7' => '1',
            'store_opening_time' => '08:00',
            'store_closing_time' => '22:00',
            'store_status' => 'open',
            'store_is_verified' => '1',
            'store_is_active' => '1',
        ])->assertRedirect(route('admin.vendors.index'));

        $vendor = User::where('phone', '0533333333')->firstOrFail();

        expect($vendor)
            ->role->toBe('vendor')
            ->name->toBe('New Vendor')
            ->is_active->toBeTrue()
            ->code->toBeNull()
            ->and($vendor->phone_verified_at)->not->toBeNull()
            ->and(Hash::check('password123', $vendor->password))->toBeTrue()
            ->and($vendor->stores)->toHaveCount(1);

        $store = $vendor->stores->first();

        expect($store)
            ->name_ar->toBe('متجر النور')
            ->name_en->toBe('Al Noor Grocery')
            ->category_id->toBe($category->id)
            ->city_id->toBe($city->id)
            ->address->toBe('الرياض، حي العليا')
            ->phone->toBe('0112345678')
            ->email->toBe('store@zadon.sa')
            ->cr_number->toBe('1010123456')
            ->vat_number->toBe('310121234500003')
            ->manager_name->toBe('Ali Hassan')
            ->prep_time_min->toBe(20)
            ->is_open_24_7->toBeTrue()
            ->opening_time->toBe('08:00')
            ->closing_time->toBe('22:00')
            ->status->toBe('open')
            ->is_verified->toBeTrue()
            ->is_active->toBeTrue()
            ->delivery_fee->toEqual(15.5)
            ->min_order->toEqual(25.0)
            ->delivery_radius_km->toEqual(12.0);
    });

    it('requires the fields the register endpoint requires', function (string $field, array $overrides) {
        $payload = array_merge([
            'name' => 'Incomplete Vendor',
            'phone' => '0534444444',
            'password' => 'password123',
            'store_name_ar' => 'متجر',
            'store_city_id' => City::factory()->create()->id,
            'store_address' => 'الرياض',
        ], $overrides);

        $payload = collect($payload)->except($field)->all();

        $this->post(route('admin.vendors.store'), $payload)->assertSessionHasErrors($field);

        expect(User::where('phone', '0534444444')->exists())->toBeFalse();
    })->with([
        ['phone', []],
        ['password', []],
        ['store_name_ar', []],
        ['store_city_id', []],
        ['store_address', []],
    ]);

    it('rejects a duplicate phone and email', function () {
        User::factory()->create(['phone' => '0544444444', 'email' => 'taken@zadon.sa']);

        $payload = [
            'name' => 'Duplicate Vendor',
            'phone' => '0544444444',
            'email' => 'taken@zadon.sa',
            'password' => 'password123',
            'store_name_ar' => 'متجر',
            'store_city_id' => City::factory()->create()->id,
            'store_address' => 'الرياض',
        ];

        $this->post(route('admin.vendors.store'), $payload)
            ->assertSessionHasErrors(['phone', 'email']);
    });

    it('uploads the store logo and cover image', function () {
        Storage::fake('public');

        $this->post(route('admin.vendors.store'), [
            'name' => 'With Images',
            'phone' => '0510101010',
            'password' => 'password123',
            'store_name_ar' => 'متجر الصور',
            'store_city_id' => City::factory()->create()->id,
            'store_address' => 'الرياض',
            'store_logo' => UploadedFile::fake()->create('logo.jpg', 10, 'image/jpeg'),
            'store_cover_image' => UploadedFile::fake()->create('cover.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.vendors.index'));

        $store = User::where('phone', '0510101010')->firstOrFail()->stores->first();

        expect($store->logo)->toStartWith('stores/')
            ->and($store->cover_image)->toStartWith('stores/');

        Storage::disk('public')->assertExists($store->logo);
        Storage::disk('public')->assertExists($store->cover_image);
    });

    it('renders the edit form', function () {
        $vendor = User::factory()->create(['role' => 'vendor']);
        Store::factory()->create(['owner_id' => $vendor->id]);

        $this->get(route('admin.vendors.edit', $vendor))->assertOk()->assertSee($vendor->name);
    });

    it('updates the vendor account and store', function () {
        $vendor = User::factory()->create(['name' => 'Old Name', 'role' => 'vendor']);
        $store = Store::factory()->create(['owner_id' => $vendor->id, 'name_ar' => 'متجر قديم', 'is_verified' => false]);

        $this->put(route('admin.vendors.update', $vendor), [
            'name' => 'New Name',
            'phone' => $vendor->phone ?? '0510202020',
            'email' => $vendor->email,
            'store_name_ar' => 'متجر جديد',
            'store_city_id' => $store->city_id,
            'store_address' => 'جدة، حي الروضة',
            'store_status' => 'busy',
            'store_is_verified' => '1',
        ])->assertRedirect(route('admin.vendors.index'));

        expect($vendor->refresh())->name->toBe('New Name')->role->toBe('vendor');

        expect($store->refresh())
            ->name_ar->toBe('متجر جديد')
            ->address->toBe('جدة، حي الروضة')
            ->status->toBe('busy')
            ->is_verified->toBeTrue();
    });

    it('keeps the current password when the field is left blank', function () {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $hash = $vendor->password;

        $this->put(route('admin.vendors.update', $vendor), [
            'name' => $vendor->name,
            'phone' => $vendor->phone ?? '0510303030',
            'email' => $vendor->email,
            'password' => '',
            'store_name_ar' => 'متجر',
            'store_city_id' => City::factory()->create()->id,
            'store_address' => 'الرياض',
        ])->assertRedirect(route('admin.vendors.index'));

        expect($vendor->refresh()->password)->toBe($hash);
    });

    it('cannot be managed through the customers or admins sections', function (string $section) {
        $vendor = User::factory()->create(['name' => 'The Vendor', 'role' => 'vendor']);

        $this->get(route("admin.{$section}.edit", $vendor))->assertNotFound();
        $this->patch(route("admin.{$section}.toggle", $vendor))->assertNotFound();
        $this->delete(route("admin.{$section}.destroy", $vendor))->assertNotFound();

        expect(User::whereKey($vendor->id)->exists())->toBeTrue();
    })->with(['users', 'admins']);

    it('toggles a vendor status', function () {
        $vendor = User::factory()->create(['role' => 'vendor', 'is_active' => true]);

        $this->patch(route('admin.vendors.toggle', $vendor))->assertRedirect(route('admin.vendors.index'));

        expect($vendor->refresh()->is_active)->toBeFalse();
    });

    it('deletes a vendor and its store', function () {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $store = Store::factory()->create(['owner_id' => $vendor->id]);

        $this->delete(route('admin.vendors.destroy', $vendor))->assertRedirect(route('admin.vendors.index'));

        expect(User::whereKey($vendor->id)->exists())->toBeFalse()
            ->and(Store::whereKey($store->id)->exists())->toBeFalse();
    });

    it('refuses to delete a vendor whose store has products', function () {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $store = Store::factory()->create(['owner_id' => $vendor->id]);
        Product::factory()->create(['store_id' => $store->id]);

        $this->delete(route('admin.vendors.destroy', $vendor))->assertSessionHas('error');

        expect(User::whereKey($vendor->id)->exists())->toBeTrue()
            ->and(Store::whereKey($store->id)->exists())->toBeTrue();
    });
});
