<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function (string $path) {
    $this->get($path)->assertRedirect(route('admin.login'));
})->with(['/admin/cities', '/admin/categories']);

it('forbids authenticated non admins', function (string $path) {
    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get($path)
        ->assertForbidden();
})->with(['/admin/cities', '/admin/categories']);

describe('city management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists cities', function () {
        City::factory()->create(['name_ar' => 'الاسكندرية', 'name_en' => 'Alexandria']);

        $this->get(route('admin.cities.index'))
            ->assertOk()
            ->assertSee('الاسكندرية')
            ->assertSee('Alexandria');
    });

    it('searches cities', function () {
        City::factory()->create(['name_ar' => 'القاهرة', 'name_en' => 'Cairo']);
        City::factory()->create(['name_ar' => 'طنطا', 'name_en' => 'Tanta']);

        $this->get(route('admin.cities.index', ['search' => 'Cairo']))
            ->assertOk()
            ->assertSee('Cairo')
            ->assertDontSee('Tanta');
    });

    it('filters cities by status', function () {
        City::factory()->create(['name_ar' => 'القاهرة', 'is_active' => true]);
        City::factory()->inactive()->create(['name_ar' => 'طنطا']);

        $this->get(route('admin.cities.index', ['is_active' => '0']))
            ->assertOk()
            ->assertSee('طنطا')
            ->assertDontSee('القاهرة');
    });

    it('renders the create form', function () {
        $this->get(route('admin.cities.create'))->assertOk();
    });

    it('creates a city', function () {
        $this->post(route('admin.cities.store'), [
            'name_ar' => 'المنصورة',
            'name_en' => 'Mansoura',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('admin.cities.index'));

        expect(City::where('name_ar', 'المنصورة')->exists())->toBeTrue();
    });

    it('requires the arabic name', function () {
        $this->post(route('admin.cities.store'), ['name_en' => 'Nowhere'])
            ->assertSessionHasErrors('name_ar');
    });

    it('updates a city', function () {
        $city = City::factory()->create(['name_ar' => 'أسيوط', 'is_active' => true]);

        $this->put(route('admin.cities.update', $city), [
            'name_ar' => 'أسيوط الجديدة',
            'is_active' => '0',
        ])->assertRedirect(route('admin.cities.index'));

        expect($city->refresh())
            ->name_ar->toBe('أسيوط الجديدة')
            ->is_active->toBeFalse();
    });

    it('toggles a city status', function () {
        $city = City::factory()->create(['is_active' => true]);

        $this->patch(route('admin.cities.toggle', $city))->assertRedirect();

        expect($city->refresh()->is_active)->toBeFalse();
    });

    it('deletes an unused city', function () {
        $city = City::factory()->create();

        $this->delete(route('admin.cities.destroy', $city))->assertRedirect();

        expect(City::whereKey($city->id)->exists())->toBeFalse();
    });

    it('refuses to delete a city used by a store', function () {
        $city = City::factory()->create();
        Store::factory()->create(['city_id' => $city->id]);

        $this->delete(route('admin.cities.destroy', $city))
            ->assertSessionHas('error');

        expect(City::whereKey($city->id)->exists())->toBeTrue();
    });

    it('refuses to delete a city used by a user', function () {
        $city = City::factory()->create();
        User::factory()->create(['city_id' => $city->id]);

        $this->delete(route('admin.cities.destroy', $city))
            ->assertSessionHas('error');

        expect(City::whereKey($city->id)->exists())->toBeTrue();
    });
});

describe('category management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists categories', function () {
        Category::factory()->forStores()->create(['name_ar' => 'بقالة', 'slug' => 'groceries']);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('بقالة')
            ->assertSee('groceries');
    });

    it('filters categories by type', function () {
        Category::factory()->forStores()->create(['name_ar' => 'مخابز', 'slug' => 'bakeries']);
        Category::factory()->forProducts()->create(['name_ar' => 'ألبان', 'slug' => 'dairy']);

        $this->get(route('admin.categories.index', ['type' => 'product']))
            ->assertOk()
            ->assertSee('ألبان')
            ->assertDontSee('مخابز');
    });

    it('shows store and product usage counts', function () {
        $category = Category::factory()->forStores()->create();
        Store::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Stores: 1')
            ->assertSee('Products: 0');
    });

    it('creates a category and generates a slug from the english name', function () {
        $this->post(route('admin.categories.store'), [
            'type' => 'store',
            'name_ar' => 'مخابز',
            'name_en' => 'Bakeries',
        ])->assertRedirect(route('admin.categories.index'));

        expect(Category::where('slug', 'bakeries')->exists())->toBeTrue();
    });

    it('falls back to a usable slug when the name has no latin characters', function () {
        $this->post(route('admin.categories.store'), [
            'type' => 'store',
            'name_ar' => 'حلويات',
        ])->assertRedirect(route('admin.categories.index'));

        expect(Category::where('name_ar', 'حلويات')->value('slug'))->not->toBeEmpty();
    });

    it('keeps generated slugs unique', function () {
        Category::factory()->forStores()->create(['slug' => 'groceries']);

        $this->post(route('admin.categories.store'), [
            'type' => 'store',
            'name_ar' => 'بقالة أخرى',
            'name_en' => 'Groceries',
        ])->assertRedirect(route('admin.categories.index'));

        expect(Category::where('slug', 'groceries')->count())->toBe(1)
            ->and(Category::where('slug', 'groceries-2')->exists())->toBeTrue();
    });

    it('rejects an invalid type', function () {
        $this->post(route('admin.categories.store'), [
            'type' => 'nope',
            'name_ar' => 'شيء',
        ])->assertSessionHasErrors('type');
    });

    it('rejects a parent category from a different type', function () {
        $parent = Category::factory()->forProducts()->create();

        $this->post(route('admin.categories.store'), [
            'type' => 'store',
            'name_ar' => 'بقالة',
            'parent_id' => $parent->id,
        ])->assertSessionHasErrors('parent_id');
    });

    it('rejects itself as its own parent', function () {
        $category = Category::factory()->forStores()->create();

        $this->put(route('admin.categories.update', $category), [
            'type' => 'store',
            'name_ar' => $category->name_ar,
            'parent_id' => $category->id,
        ])->assertSessionHasErrors('parent_id');
    });

    it('updates a category without changing its slug', function () {
        $category = Category::factory()->forStores()->create(['name_ar' => 'مقاهي', 'slug' => 'cafes']);

        $this->put(route('admin.categories.update', $category), [
            'type' => 'store',
            'name_ar' => 'مقاهي ومسالق',
        ])->assertRedirect(route('admin.categories.index'));

        expect($category->refresh())
            ->name_ar->toBe('مقاهي ومسالق')
            ->slug->toBe('cafes');
    });

    it('toggles a category status', function () {
        $category = Category::factory()->create(['is_active' => true]);

        $this->patch(route('admin.categories.toggle', $category))->assertRedirect();

        expect($category->refresh()->is_active)->toBeFalse();
    });

    it('refuses to delete a category that has children', function () {
        $parent = Category::factory()->forStores()->create();
        Category::factory()->forStores()->create(['parent_id' => $parent->id]);

        $this->delete(route('admin.categories.destroy', $parent))
            ->assertSessionHas('error');

        expect(Category::whereKey($parent->id)->exists())->toBeTrue();
    });

    it('refuses to delete a category in use by a store', function () {
        $category = Category::factory()->forStores()->create();
        Store::factory()->create(['category_id' => $category->id]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        expect(Category::whereKey($category->id)->exists())->toBeTrue();
    });

    it('deletes an unused category', function () {
        $category = Category::factory()->forStores()->create();

        $this->delete(route('admin.categories.destroy', $category))->assertRedirect();

        expect(Category::whereKey($category->id)->exists())->toBeFalse();
    });
});
