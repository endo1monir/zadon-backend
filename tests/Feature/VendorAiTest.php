<?php

use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\User;
use App\Support\AiAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Laravel\Sanctum\Sanctum;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Exceptions\PrismProviderOverloadedException;
use Prism\Prism\Exceptions\PrismRateLimitedException;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->vendor = User::create([
        'name' => 'Honey Shop Owner',
        'phone' => '0555123456',
        'email' => 'vendor@example.com',
        'password' => 'password123',
        'role' => 'vendor',
    ]);

    $this->category = Category::create([
        'type' => 'store',
        'name_ar' => 'عسل',
        'name_en' => 'Honey',
        'slug' => 'honey',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->city = City::create([
        'name_ar' => 'الرياض',
        'name_en' => 'Riyadh',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $this->store = $this->vendor->stores()->create([
        'name_ar' => 'متجر العسل',
        'name_en' => 'Honey Store',
        'category_id' => $this->category->id,
        'city_id' => $this->city->id,
        'address' => 'الرياض، حي النخيل',
        'is_verified' => true,
    ]);

    $this->product = Product::create([
        'store_id' => $this->store->id,
        'category_id' => $this->category->id,
        'name_ar' => 'عسل سدر',
        'name_en' => 'Sidr Honey',
        'sku' => 'HNY-SIDR',
        'price' => 120,
        'stock' => 10,
    ]);
});

test('a vendor can generate text', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $fake = Prism::fake([TextResponseFake::make()->withText('مرحباً بك')]);

    $this->postJson('/api/vendor/ai/generate-text', [
        'prompt' => 'قل مرحبا',
        'system_prompt' => 'رد بالعربي',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.text', 'مرحباً بك');

    $fake->assertPrompt('قل مرحبا');
});

test('generate text requires a prompt', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $fake = Prism::fake();

    $this->postJson('/api/vendor/ai/generate-text', ['prompt' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('prompt');

    $fake->assertCallCount(0);
});

test('guests cannot use the ai endpoints', function () {
    $this->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])->assertUnauthorized();
    $this->postJson("/api/vendor/products/{$this->product->id}/ai-descriptions")->assertUnauthorized();
});

test('a token without the vendor ability cannot use the ai endpoints', function () {
    Sanctum::actingAs($this->vendor, ['customer']);

    $this->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])->assertForbidden();
});

test('a vendor gets suggested descriptions for their own product without saving them', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    Prism::fake([
        StructuredResponseFake::make()->withStructured([
            'description_ar' => 'عسل سدر طبيعي.',
            'description_en' => 'Natural Sidr honey.',
        ]),
    ]);

    $this->postJson("/api/vendor/products/{$this->product->id}/ai-descriptions")
        ->assertOk()
        ->assertJsonPath('data.product_id', $this->product->id)
        ->assertJsonPath('data.descriptions.description_ar', 'عسل سدر طبيعي.')
        ->assertJsonPath('data.descriptions.description_en', 'Natural Sidr honey.');

    expect($this->product->fresh()->description_en)->toBeNull();
});

test('a vendor cannot generate descriptions for another store product', function () {
    $otherVendor = User::create([
        'name' => 'Other Owner',
        'phone' => '0555999888',
        'email' => 'other@example.com',
        'password' => 'password123',
        'role' => 'vendor',
    ]);
    $otherStore = $otherVendor->stores()->create([
        'name_ar' => 'متجر آخر',
        'name_en' => 'Other Store',
        'category_id' => $this->category->id,
        'city_id' => $this->city->id,
        'address' => 'جدة',
        'is_verified' => true,
    ]);
    $otherProduct = Product::create([
        'store_id' => $otherStore->id,
        'category_id' => $this->category->id,
        'name_ar' => 'تمر',
        'name_en' => 'Dates',
        'sku' => 'DATES',
        'price' => 40,
        'stock' => 5,
    ]);

    Sanctum::actingAs($this->vendor, ['vendor']);
    $fake = Prism::fake();

    $this->postJson("/api/vendor/products/{$otherProduct->id}/ai-descriptions")->assertNotFound();

    $fake->assertCallCount(0);
});

test('a provider rate limit returns 429', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $this->mock(AiAssistant::class)
        ->shouldReceive('generateText')
        ->andThrow(PrismRateLimitedException::make());

    $this->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])
        ->assertStatus(429)
        ->assertJsonPath('success', false);
});

test('a provider failure returns 503', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $this->mock(AiAssistant::class)
        ->shouldReceive('generateProductDescriptions')
        ->andThrow(new PrismException('Provider is down'));

    $this->postJson("/api/vendor/products/{$this->product->id}/ai-descriptions")
        ->assertStatus(503)
        ->assertJsonPath('success', false);
});

test('an overloaded provider returns 503 with the busy message', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $this->mock(AiAssistant::class)
        ->shouldReceive('generateText')
        ->andThrow(PrismProviderOverloadedException::make('gemini'));

    $this->withHeader('lang', 'en')
        ->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', trans('messages.ai_rate_limited', [], 'en'));
});

test('a provider timeout returns 503 instead of a server error', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    $this->mock(AiAssistant::class)
        ->shouldReceive('generateText')
        ->andThrow(new ConnectionException('cURL error 28: Operation timed out'));

    $this->withHeader('lang', 'en')
        ->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', trans('messages.ai_unavailable', [], 'en'));
});

test('ai endpoints are limited to 10 requests per minute per vendor', function () {
    Sanctum::actingAs($this->vendor, ['vendor']);
    Prism::fake(array_fill(0, 10, TextResponseFake::make()->withText('ok')));

    for ($attempt = 1; $attempt <= 10; $attempt++) {
        $this->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])->assertOk();
    }

    $this->postJson('/api/vendor/ai/generate-text', ['prompt' => 'hello'])->assertTooManyRequests();
});
