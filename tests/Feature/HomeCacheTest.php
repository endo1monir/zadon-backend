<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function survivesCacheRoundTrip(mixed $value): mixed
{
    return unserialize(serialize($value), ['allowed_classes' => config('cache.serializable_classes')]);
}

function payloadFor(string $label): EloquentCollection
{
    return match ($label) {
        'banners' => Banner::query()->orderBy('id')->get(),
        'categories' => Category::query()->active()->whereNull('parent_id')->orderBy('sort_order')->get(),
        'stores' => Store::query()->active()->with('category')->orderByDesc('rating')->limit(5)->get(),
    };
}

test('the cache allowlist is not disabled wholesale', function () {
    expect(config('cache.serializable_classes'))->toBeArray()->not->toBeEmpty();
});

test('every class cached by the home queries is on the allowlist', function (string $label) {
    $allowed = config('cache.serializable_classes');

    $payload = payloadFor($label);

    preg_match_all('/O:\d+:"([^"]+)"/', serialize($payload), $matches);

    $missing = array_values(array_diff(array_unique($matches[1]), $allowed));

    expect($missing)->toBe([], 'these classes are cached by the home '.$label.' query but are not on cache.serializable_classes');
})->with(['banners', 'categories', 'stores']);

test('cached home payloads unserialize into real objects not incomplete classes', function (string $label) {
    $restored = survivesCacheRoundTrip(payloadFor($label));

    expect($restored)->toBeInstanceOf(EloquentCollection::class)
        ->and($restored)->not->toBeInstanceOf(__PHP_Incomplete_Class::class)
        ->and($restored->first())->not->toBeInstanceOf(__PHP_Incomplete_Class::class);
})->with(['banners', 'categories', 'stores']);

test('a disabled allowlist turns cached models into incomplete classes', function () {
    $collection = Banner::query()->get();

    $restored = unserialize(serialize($collection), ['allowed_classes' => false]);

    expect($restored)->toBeInstanceOf(__PHP_Incomplete_Class::class);
});

test('the home endpoint responds on both a cold and a warm cache', function () {
    $this->getJson('/api/home')->assertOk()->assertJsonStructure([
        'data' => ['banners', 'categories', 'featured_stores'],
    ]);

    $this->getJson('/api/home')->assertOk()->assertJsonStructure([
        'data' => ['banners', 'categories', 'featured_stores'],
    ]);
});
