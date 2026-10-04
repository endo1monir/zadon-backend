<?php

use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to the admin login', function () {
    $this->get(route('admin.reviews.index'))->assertRedirect(route('admin.login'));
});

it('forbids authenticated non admins', function () {
    $this->actingAs(User::factory()->create(['role' => 'customer']))
        ->get(route('admin.reviews.index'))
        ->assertForbidden();
});

describe('review management', function () {
    beforeEach(fn () => actingAsAdmin());

    it('lists store reviews with the customer and the store', function () {
        $store = Store::factory()->create(['name_ar' => 'بقالة النور']);
        $review = Review::factory()->forStore($store)->create([
            'customer_name' => 'Sarah Ahmed',
            'comment' => 'Great prices and fast delivery',
            'rating' => 5,
        ]);

        $this->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('بقالة النور')
            ->assertSee('Sarah Ahmed')
            ->assertSee('Great prices and fast delivery');
    });

    it('summarises the reviews', function () {
        $store = Store::factory()->create();
        Review::factory()->count(2)->forStore($store)->create(['rating' => 4]);
        Review::factory()->forStore($store)->create(['rating' => 2, 'published' => false, 'store_reply' => 'Thanks!']);

        $this->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Total reviews', escape: false)
            ->assertSee('3.3');
    });

    it('searches reviews by customer, comment, store and order number', function (string $term, string $expected, string $hidden) {
        $store = Store::factory()->create(['name_ar' => 'مخبز الحي']);
        Review::factory()->forStore($store)->create([
            'customer_name' => 'Sarah Ahmed',
            'comment' => 'The bread was fresh',
        ]);

        Review::factory()->create([
            'store_id' => Store::factory()->create()->id,
            'customer_name' => 'Khaled Omar',
            'comment' => 'Nothing special',
        ]);

        $this->get(route('admin.reviews.index', ['search' => $term]))
            ->assertOk()
            ->assertSee($expected)
            ->assertDontSee($hidden);
    })->with([
        ['Sarah', 'Sarah Ahmed', 'Khaled Omar'],
        ['fresh', 'The bread was fresh', 'Nothing special'],
        ['مخبز', 'مخبز الحي', 'Nothing special'],
    ]);

    it('filters reviews by store', function () {
        $store = Store::factory()->create();
        Review::factory()->forStore($store)->create(['customer_name' => 'Store One Customer']);
        Review::factory()->create(['customer_name' => 'Other Store Customer']);

        $this->get(route('admin.reviews.index', ['store_id' => $store->id]))
            ->assertOk()
            ->assertSee('Store One Customer')
            ->assertDontSee('Other Store Customer');
    });

    it('filters reviews by rating', function () {
        $store = Store::factory()->create();
        Review::factory()->forStore($store)->create(['customer_name' => 'Five Star Customer', 'rating' => 5]);
        Review::factory()->forStore($store)->create(['customer_name' => 'One Star Customer', 'rating' => 1]);

        $this->get(route('admin.reviews.index', ['rating' => 1]))
            ->assertOk()
            ->assertSee('One Star Customer')
            ->assertDontSee('Five Star Customer');
    });

    it('filters reviews by visibility', function () {
        $store = Store::factory()->create();
        Review::factory()->forStore($store)->create(['customer_name' => 'Visible Customer', 'published' => true]);
        Review::factory()->forStore($store)->create(['customer_name' => 'Hidden Customer', 'published' => false]);

        $this->get(route('admin.reviews.index', ['published' => '0']))
            ->assertOk()
            ->assertSee('Hidden Customer')
            ->assertDontSee('Visible Customer');
    });

    it('filters reviews by whether the store replied', function () {
        $store = Store::factory()->create();
        Review::factory()->forStore($store)->create(['customer_name' => 'Replied Customer']);
        Review::factory()->forStore($store)->replied()->create(['customer_name' => 'Silent Customer']);

        $this->get(route('admin.reviews.index', ['replied' => '0']))
            ->assertOk()
            ->assertSee('Replied Customer')
            ->assertDontSee('Silent Customer');
    });

    it('toggles the visibility of a review', function () {
        $review = Review::factory()->create(['published' => true]);

        $this->patch(route('admin.reviews.toggle', $review))->assertRedirect();

        expect($review->refresh()->published)->toBeFalse();
    });

    it('deletes a review', function () {
        $review = Review::factory()->create();

        $this->delete(route('admin.reviews.destroy', $review))->assertRedirect();

        expect(Review::whereKey($review->id)->exists())->toBeFalse();
    });
});
