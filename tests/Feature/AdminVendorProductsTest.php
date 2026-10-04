<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * A vendor with the store the admin dashboard manages, mirroring how the vendor
 * API resolves the store it owns.
 */
function vendorWithStore(array $vendorAttributes = [], array $storeAttributes = []): array
{
    $vendor = User::factory()->create(['role' => 'vendor', ...$vendorAttributes]);
    $store = Store::factory()->create(['owner_id' => $vendor->id, ...$storeAttributes]);

    return [$vendor, $store];
}

/**
 * The vendor product list url, keeping any filters in the query string.
 */
function productsUrl(User $vendor, array $query = []): string
{
    return route('admin.vendors.products.index', ['vendor' => $vendor->id, ...$query]);
}

it('redirects guests to the admin login', function () {
    [$vendor] = vendorWithStore();

    $this->get(route('admin.vendors.products.index', $vendor))->assertRedirect(route('admin.login'));
});

it('forbids authenticated non admins', function () {
    [$vendor] = vendorWithStore();

    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get(route('admin.vendors.products.index', $vendor))
        ->assertForbidden();
});

it('cannot reach the products of a user who is not a vendor', function (string $role) {
    actingAsAdmin();

    $user = User::factory()->create(['role' => $role]);
    Store::factory()->create(['owner_id' => $user->id]);

    $this->get(route('admin.vendors.products.index', $user))->assertNotFound();
})->with(['customer', 'admin']);

describe('vendor product management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists the products of the vendor store', function () {
        [$vendor, $store] = vendorWithStore(['name' => 'Store Owner'], ['name_ar' => 'متجر النور']);
        Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'لبن جهينة', 'name_en' => 'Juhaina Milk']);

        $this->get(route('admin.vendors.products.index', $vendor))
            ->assertOk()
            ->assertSee('لبن جهينة')
            ->assertSee('Juhaina Milk')
            ->assertSee('متجر النور');
    });

    it('never lists products belonging to another store', function () {
        [$vendor, $store] = vendorWithStore();
        Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'منتج Our Store']);

        $otherStore = Store::factory()->create();
        Product::factory()->create(['store_id' => $otherStore->id, 'name_ar' => 'منتج Other Store']);

        $this->get(route('admin.vendors.products.index', $vendor))
            ->assertOk()
            ->assertSee('منتج Our Store')
            ->assertDontSee('منتج Other Store');
    });

    it('shows the product count on the vendor and links to the products', function () {
        [$vendor, $store] = vendorWithStore();
        Product::factory()->count(3)->create(['store_id' => $store->id]);

        $this->get(route('admin.vendors.index'))
            ->assertOk()
            ->assertSee(route('admin.vendors.products.index', $vendor))
            ->assertSee('3');
    });

    it('searches products by name and sku', function () {
        [$vendor, $store] = vendorWithStore();
        Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'لبن جهينة', 'sku' => 'MILK-1L']);
        Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'أرز بسمتي', 'sku' => 'RICE-5K']);

        $this->get(productsUrl($vendor, ['search' => 'MILK']))
            ->assertOk()
            ->assertSee('MILK-1L')
            ->assertDontSee('RICE-5K');
    });

    it('filters products by category', function () {
        [$vendor, $store] = vendorWithStore();
        $category = Category::factory()->create(['type' => 'product']);
        Product::factory()->create(['store_id' => $store->id, 'category_id' => $category->id, 'name_ar' => 'مصنف']);
        Product::factory()->create(['store_id' => $store->id, 'category_id' => null, 'name_ar' => 'بدون تصنيف']);

        $this->get(productsUrl($vendor, ['category_id' => $category->id]))
            ->assertOk()
            ->assertSee('مصنف')
            ->assertDontSee('بدون تصنيف');
    });

    it('filters products by low and out of stock', function () {
        [$vendor, $store] = vendorWithStore();
        Product::factory()->lowStock()->create(['store_id' => $store->id, 'name_ar' => 'قارب على النفاد']);
        Product::factory()->outOfStock()->create(['store_id' => $store->id, 'name_ar' => 'نفد من المخزون']);
        Product::factory()->create(['store_id' => $store->id, 'stock' => 90, 'name_ar' => 'متوفر']);

        $this->get(productsUrl($vendor, ['stock' => 'out']))
            ->assertOk()
            ->assertSee('نفد من المخزون')
            ->assertDontSee('قارب على النفاد')
            ->assertDontSee('متوفر');

        $this->get(productsUrl($vendor, ['stock' => 'low']))
            ->assertOk()
            ->assertSee('قارب على النفاد')
            ->assertDontSee('متوفر');
    });

    it('filters products by status', function () {
        [$vendor, $store] = vendorWithStore();
        Product::factory()->inactive()->create(['store_id' => $store->id, 'name_ar' => 'منتج موقوف']);
        Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'منتج معروض']);

        $this->get(productsUrl($vendor, ['is_active' => '0']))
            ->assertOk()
            ->assertSee('منتج موقوف')
            ->assertDontSee('منتج معرض');
    });

    it('renders the create form', function () {
        [$vendor] = vendorWithStore();

        $this->get(route('admin.vendors.products.create', $vendor))->assertOk();
    });

    it('creates a product with the same fields the vendor add product endpoint accepts', function () {
        [$vendor, $store] = vendorWithStore();
        $category = Category::factory()->create(['type' => 'product']);

        $this->post(route('admin.vendors.products.store', $vendor), [
            'name_ar' => 'لبن جهينة',
            'name_en' => 'Juhaina Milk',
            'description_ar' => 'لبن كامل الدسم',
            'description_en' => 'Full fat milk',
            'category_id' => $category->id,
            'sku' => 'MILK-1L',
            'barcode' => '6281000112233',
            'price' => '12.50',
            'original_price' => '15.00',
            'cost_price' => '9.00',
            'stock' => '40',
            'min_stock_alert' => '5',
            'unit_ar' => 'علبة',
            'unit_en' => 'box',
            'country_of_origin' => 'السعودية',
            'storage_method' => 'مبرد',
            'storage_temp' => 'chilled',
            'expiry_date' => '2026-12-31',
            'is_active' => '1',
            'is_prescription_required' => '0',
            'options' => '[{"name":"1 kg","price_surplus":2.5}]',
        ])->assertRedirect(route('admin.vendors.products.index', $vendor));

        $product = Product::where('sku', 'MILK-1L')->firstOrFail();

        expect($product->store_id)->toBe($store->id)
            ->and($product->name_ar)->toBe('لبن جهينة')
            ->and($product->name_en)->toBe('Juhaina Milk')
            ->and($product->description_ar)->toBe('لبن كامل الدسم')
            ->and($product->description_en)->toBe('Full fat milk')
            ->and($product->category_id)->toBe($category->id)
            ->and($product->barcode)->toBe('6281000112233')
            ->and((float) $product->price)->toBe(12.5)
            ->and((float) $product->original_price)->toBe(15.0)
            ->and((float) $product->cost_price)->toBe(9.0)
            ->and($product->stock)->toBe(40)
            ->and($product->min_stock_alert)->toBe(5)
            ->and($product->unit_ar)->toBe('علبة')
            ->and($product->unit_en)->toBe('box')
            ->and($product->country_of_origin)->toBe('السعودية')
            ->and($product->storage_method)->toBe('مبرد')
            ->and($product->storage_temp)->toBe('chilled')
            ->and($product->expiry_date->format('Y-m-d'))->toBe('2026-12-31')
            ->and($product->is_active)->toBeTrue()
            ->and($product->is_prescription_required)->toBeFalse()
            ->and($product->options)->toBe([['name' => '1 kg', 'price_surplus' => 2.5]]);
    });

    it('requires the fields the vendor add product endpoint requires', function (string $field) {
        [$vendor] = vendorWithStore();

        $payload = [
            'name_ar' => 'لبن جهينة',
            'price' => '12.50',
            'unit_ar' => 'علبة',
        ];

        unset($payload[$field]);

        $this->post(route('admin.vendors.products.store', $vendor), $payload)
            ->assertSessionHasErrors($field);

        expect(Product::where('name_ar', 'لبن جهينة')->exists())->toBeFalse();
    })->with(['name_ar', 'price', 'unit_ar']);

    it('rejects an unsupported storage temperature', function () {
        [$vendor] = vendorWithStore();

        $this->post(route('admin.vendors.products.store', $vendor), [
            'name_ar' => 'منتج',
            'price' => '5',
            'unit_ar' => 'قطعة',
            'storage_temp' => 'frozen-hard',
        ])->assertSessionHasErrors('storage_temp');
    });

    it('rejects options that are not valid json', function () {
        [$vendor] = vendorWithStore();

        $this->post(route('admin.vendors.products.store', $vendor), [
            'name_ar' => 'منتج',
            'price' => '5',
            'unit_ar' => 'قطعة',
            'options' => 'not json',
        ])->assertSessionHasErrors('options');
    });

    it('uploads the product image', function () {
        Storage::fake('public');
        [$vendor] = vendorWithStore();

        $this->post(route('admin.vendors.products.store', $vendor), [
            'name_ar' => 'منتج بالصورة',
            'price' => '5',
            'unit_ar' => 'قطعة',
            'image' => UploadedFile::fake()->create('milk.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.vendors.products.index', $vendor));

        $product = Product::where('name_ar', 'منتج بالصورة')->firstOrFail();

        expect($product->image)->toStartWith('products/');
        Storage::disk('public')->assertExists($product->image);
    });

    it('renders the edit form with the current product', function () {
        [$vendor, $store] = vendorWithStore();
        $product = Product::factory()->create(['store_id' => $store->id, 'name_ar' => 'منتج للتعديل']);

        $this->get(route('admin.vendors.products.edit', [$vendor, $product]))
            ->assertOk()
            ->assertSee('منتج للتعديل');
    });

    it('updates a product and replaces its image', function () {
        Storage::fake('public');
        [$vendor, $store] = vendorWithStore();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'name_ar' => 'الاسم القديم',
            'image' => 'products/old.jpg',
        ]);
        Storage::disk('public')->put('products/old.jpg', 'x');

        $this->put(route('admin.vendors.products.update', [$vendor, $product]), [
            'name_ar' => 'الاسم الجديد',
            'price' => '20',
            'unit_ar' => 'كيلو',
            'stock' => '0',
            'is_active' => '0',
            'image' => UploadedFile::fake()->create('new.jpg', 10, 'image/jpeg'),
        ])->assertRedirect(route('admin.vendors.products.index', $vendor));

        expect($product->refresh())
            ->name_ar->toBe('الاسم الجديد')
            ->stock->toBe(0)
            ->is_active->toBeFalse();

        expect($product->image)->not->toBe('products/old.jpg');
        Storage::disk('public')->assertMissing('products/old.jpg');
        Storage::disk('public')->assertExists($product->image);
    });

    it('keeps the current image and stock when the fields are omitted', function () {
        [$vendor, $store] = vendorWithStore();
        $product = Product::factory()->create([
            'store_id' => $store->id,
            'image' => 'products/keep.jpg',
            'stock' => 17,
            'min_stock_alert' => 4,
        ]);

        $this->put(route('admin.vendors.products.update', [$vendor, $product]), [
            'name_ar' => 'الاسم الجديد',
            'price' => '20',
            'unit_ar' => 'كيلو',
        ])->assertRedirect(route('admin.vendors.products.index', $vendor));

        expect($product->refresh())
            ->image->toBe('products/keep.jpg')
            ->stock->toBe(17)
            ->min_stock_alert->toBe(4);
    });

    it('toggles a product status', function () {
        [$vendor, $store] = vendorWithStore();
        $product = Product::factory()->create(['store_id' => $store->id, 'is_active' => true]);

        $this->patch(route('admin.vendors.products.toggle', [$vendor, $product]))->assertRedirect();

        expect($product->refresh()->is_active)->toBeFalse();
    });

    it('deletes a product and its image', function () {
        Storage::fake('public');
        [$vendor, $store] = vendorWithStore();
        $product = Product::factory()->create(['store_id' => $store->id, 'image' => 'products/gone.jpg']);
        Storage::disk('public')->put('products/gone.jpg', 'x');

        $this->delete(route('admin.vendors.products.destroy', [$vendor, $product]))->assertRedirect();

        expect(Product::whereKey($product->id)->exists())->toBeFalse();
        Storage::disk('public')->assertMissing('products/gone.jpg');
    });

    it('cannot reach a product that belongs to another vendor store', function () {
        [$vendor] = vendorWithStore();

        $otherStore = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $otherStore->id, 'name_ar' => 'منتج آخر']);

        $this->get(route('admin.vendors.products.edit', [$vendor, $product]))->assertNotFound();
        $this->put(route('admin.vendors.products.update', [$vendor, $product]), [
            'name_ar' => 'اختراق',
        ])->assertNotFound();
        $this->patch(route('admin.vendors.products.toggle', [$vendor, $product]))->assertNotFound();
        $this->delete(route('admin.vendors.products.destroy', [$vendor, $product]))->assertNotFound();

        expect($product->refresh()->name_ar)->toBe('منتج آخر');
    });

    it('cannot manage a product when the vendor has no store', function () {
        $vendor = User::factory()->create(['role' => 'vendor']);

        $this->get(route('admin.vendors.products.index', $vendor))->assertNotFound();
    });
});
