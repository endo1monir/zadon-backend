<?php

use App\Mcp\Servers\ZadonServer;
use App\Mcp\Tools\ListCategoriesTool;
use App\Mcp\Tools\ListOffersTool;
use App\Mcp\Tools\SearchProductsTool;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The public MCP catalog used by outside assistants such as the Gemini app.
 */
beforeEach(function () {
    $this->honey = Category::factory()->forProducts()->create(['name_ar' => 'عسل', 'name_en' => 'Honey']);
});

test('offers come back biggest discount first, without full-price or sold-out products', function () {
    Product::factory()->create(['name_en' => 'Small Deal', 'category_id' => $this->honey->id, 'price' => 90, 'original_price' => 100, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Sidr Honey', 'category_id' => $this->honey->id, 'price' => 50, 'original_price' => 100, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Full Price Honey', 'category_id' => $this->honey->id, 'price' => 80, 'original_price' => null, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Sold Out Deal', 'category_id' => $this->honey->id, 'price' => 10, 'original_price' => 100, 'stock' => 0]);

    ZadonServer::tool(ListOffersTool::class, ['language' => 'en'])
        ->assertOk()
        ->assertSee(['Sidr Honey', 'Small Deal'])
        ->assertDontSee(['Full Price Honey', 'Sold Out Deal']);

    config(['ai.chat.max_products' => 1]);

    ZadonServer::tool(ListOffersTool::class, ['language' => 'en'])
        ->assertSee('Sidr Honey')
        ->assertDontSee('Small Deal');
});

test('product names follow the requested language', function () {
    Product::factory()->create(['name_ar' => 'عسل سدر', 'name_en' => 'Sidr Honey', 'price' => 50, 'stock' => 5]);

    ZadonServer::tool(SearchProductsTool::class, ['query' => 'Sidr', 'language' => 'en'])
        ->assertOk()
        ->assertSee('Sidr Honey');

    ZadonServer::tool(SearchProductsTool::class, ['query' => 'Sidr', 'language' => 'ar'])
        ->assertOk()
        ->assertDontSee('Sidr Honey');

    expect(app()->getLocale())->toBe(config('app.locale'));
});

test('product search respects the budget', function () {
    Product::factory()->create(['name_en' => 'Honey Small', 'price' => 30, 'stock' => 5]);
    Product::factory()->create(['name_en' => 'Honey Large', 'price' => 300, 'stock' => 5]);

    ZadonServer::tool(SearchProductsTool::class, ['query' => 'Honey', 'max_price' => 100, 'language' => 'en'])
        ->assertOk()
        ->assertSee('Honey Small')
        ->assertDontSee('Honey Large');
});

test('categories are listed in both languages', function () {
    ZadonServer::tool(ListCategoriesTool::class)
        ->assertOk()
        ->assertSee(['عسل', 'Honey']);
});

test('tools are named and described from config/ai.php and marked read-only', function () {
    config(['ai.tools.list_offers.description' => 'Configured offers description.']);

    ZadonServer::tool(ListOffersTool::class)
        ->assertName('list_offers')
        ->assertDescription('Configured offers description.');

    expect((new ListOffersTool)->toArray()['annotations'])->toMatchArray(['readOnlyHint' => true]);
});

test('the server answers MCP requests over HTTP without login', function () {
    $this->postJson('/'.config('ai.mcp.path'), [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'test', 'version' => '1.0'],
        ],
    ])
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'Zadon')
        ->assertJsonPath('result.instructions', config('ai.mcp.instructions'));

    $tools = $this->postJson('/'.config('ai.mcp.path'), ['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'])
        ->assertOk()
        ->json('result.tools');

    expect(collect($tools)->pluck('name')->all())->toBe(['list_offers', 'search_products', 'list_categories']);
});
