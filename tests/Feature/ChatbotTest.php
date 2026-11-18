<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * These tests fake Google's HTTP API instead of Prism, so the real Gemini
 * handler runs and actually executes the chatbot tools against the database.
 */
beforeEach(function () {
    config([
        'ai.provider' => 'gemini',
        'ai.model' => 'gemini-flash-lite-latest',
        'prism.providers.gemini.api_key' => 'test-key',
    ]);

    $this->vitamins = Category::factory()->forProducts()->create(['name_ar' => 'فيتامينات', 'name_en' => 'Vitamins']);
    $this->honey = Category::factory()->forProducts()->create(['name_ar' => 'عسل', 'name_en' => 'Honey']);
});

test('asking for offers returns discounted products, biggest discount first', function () {
    $smallDiscount = Product::factory()->create(['name_en' => 'Vitamin C', 'category_id' => $this->vitamins->id, 'price' => 90, 'original_price' => 100, 'stock' => 10]);
    $bigDiscount = Product::factory()->create(['name_en' => 'Sidr Honey', 'category_id' => $this->honey->id, 'price' => 50, 'original_price' => 100, 'stock' => 10]);
    Product::factory()->create(['name_en' => 'Full Price Honey', 'category_id' => $this->honey->id, 'price' => 80, 'original_price' => null, 'stock' => 10]);
    Product::factory()->create(['name_en' => 'Sold Out Deal', 'category_id' => $this->honey->id, 'price' => 10, 'original_price' => 100, 'stock' => 0]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiToolCall('list_offers'))
        ->push(geminiText('Here are today\'s offers.')),
    ]);

    $response = $this->withHeader('lang', 'en')
        ->postJson('/api/chat', ['message' => 'What offers do you have?'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.reply', 'Here are today\'s offers.')
        ->assertJsonCount(2, 'data.products')
        ->assertJsonPath('data.products.0.id', $bigDiscount->id)
        ->assertJsonPath('data.products.1.id', $smallDiscount->id);

    $toolResult = geminiRequestBody(1);

    expect($toolResult)->toContain('Sidr Honey')
        ->toMatch('/discount_percent\W+50/')
        ->not->toContain('Full Price Honey')
        ->not->toContain('Sold Out Deal');
});

test('offers can be filtered by category name in either language', function () {
    Product::factory()->create(['name_en' => 'Vitamin D', 'category_id' => $this->vitamins->id, 'price' => 40, 'original_price' => 60, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Sidr Honey', 'category_id' => $this->honey->id, 'price' => 50, 'original_price' => 100, 'stock' => 5]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiToolCall('list_offers', ['category' => 'فيتامين']))
        ->push(geminiText('عروض الفيتامينات')),
    ]);

    $this->postJson('/api/chat', ['message' => 'عايز عروض الفيتامينات'])
        ->assertOk()
        ->assertJsonCount(1, 'data.products');

    expect(geminiRequestBody(1))->not->toContain('Sidr Honey');
});

test('product search respects the budget', function () {
    $cheap = Product::factory()->create(['name_en' => 'Honey Small', 'price' => 30, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Honey Large', 'price' => 300, 'stock' => 5]);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiToolCall('search_products', ['query' => 'honey', 'max_price' => 100]))
        ->push(geminiText('Found one.')),
    ]);

    $this->withHeader('lang', 'en')
        ->postJson('/api/chat', ['message' => 'Honey under 100 riyals'])
        ->assertOk()
        ->assertJsonCount(1, 'data.products')
        ->assertJsonPath('data.products.0.id', $cheap->id);
});

test('a search with no matches returns no products and tells the model', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiToolCall('search_products', ['query' => 'laptop']))
        ->push(geminiText('Sorry, we do not sell laptops.')),
    ]);

    $this->postJson('/api/chat', ['message' => 'Do you have laptops?'])
        ->assertOk()
        ->assertJsonCount(0, 'data.products');

    expect(geminiRequestBody(1))->toContain('No matching products found.');
});

test('the chatbot sends the configured prompt, its tools and the earlier messages of the conversation', function () {
    config(['ai.prompts.chat.system' => 'Be the Zadon helper. Fallback language: :language.']);

    Http::fake(['generativelanguage.googleapis.com/*' => Http::sequence()
        ->push(geminiText('وعليكم السلام'))
        ->push(geminiText('أهلاً')),
    ]);

    $conversationId = $this->withHeader('lang', 'ar')
        ->postJson('/api/chat', ['message' => 'السلام عليكم'])
        ->assertOk()
        ->json('data.conversation_id');

    $this->withHeader('lang', 'ar')
        ->postJson('/api/chat', ['message' => 'وعندكم عسل؟', 'conversation_id' => $conversationId])
        ->assertOk()
        ->assertJsonPath('data.conversation_id', $conversationId)
        ->assertJsonPath('data.reply', 'أهلاً');

    expect(geminiRequestBody(1))
        ->toContain('Be the Zadon helper. Fallback language: Arabic.')
        ->toContain('السلام عليكم')
        ->toContain('وعليكم السلام')
        ->toContain('وعندكم عسل؟')
        ->toContain('list_offers')
        ->toContain('search_products')
        ->toContain('list_categories');
});

test('chat input is validated', function (array $payload, string $field) {
    Http::fake();

    $this->postJson('/api/chat', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    Http::assertNothingSent();
})->with([
    'missing message' => [[], 'message'],
    'message too long' => [['message' => str_repeat('a', 1001)], 'message'],
    'conversation id is not a uuid' => [['message' => 'hi', 'conversation_id' => 'abc'], 'conversation_id'],
]);

test('a provider error returns 503', function () {
    Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'boom']], 500)]);

    $this->postJson('/api/chat', ['message' => 'hi'])
        ->assertStatus(503)
        ->assertJsonPath('success', false);
});
