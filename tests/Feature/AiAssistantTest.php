<?php

use App\Models\Product;
use App\Support\AiAssistant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Structured\Request as StructuredRequest;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Text\Request as TextRequest;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['ai.provider' => 'gemini', 'ai.model' => 'gemini-flash-lite-latest']);
});

test('generate text returns the model reply using the configured provider and model', function () {
    $fake = Prism::fake([TextResponseFake::make()->withText('أهلاً بك في زادون')]);

    $reply = app(AiAssistant::class)->generateText('قل مرحبا', 'رد بالعربي');

    expect($reply)->toBe('أهلاً بك في زادون');

    $fake->assertCallCount(1);
    $fake->assertRequest(function (array $requests) {
        /** @var TextRequest $request */
        $request = $requests[0];

        expect($request->provider())->toBe('gemini')
            ->and($request->model())->toBe('gemini-flash-lite-latest')
            ->and($request->prompt())->toBe('قل مرحبا')
            ->and($request->systemPrompts()[0]->content)->toBe('رد بالعربي');
    });
});

test('generate text sends no system prompt when none is given', function () {
    $fake = Prism::fake([TextResponseFake::make()->withText('ok')]);

    app(AiAssistant::class)->generateText('hello');

    $fake->assertRequest(fn (array $requests) => expect($requests[0]->systemPrompts())->toBeEmpty());
});

test('product descriptions are generated from the product details', function () {
    $product = Product::factory()->create([
        'name_ar' => 'عسل سدر',
        'name_en' => 'Sidr Honey',
        'country_of_origin' => 'Yemen',
        'storage_method' => null,
    ]);

    $fake = Prism::fake([
        StructuredResponseFake::make()->withStructured([
            'description_ar' => 'عسل سدر يمني طبيعي.',
            'description_en' => 'Natural Yemeni Sidr honey.',
        ]),
    ]);

    $descriptions = app(AiAssistant::class)->generateProductDescriptions($product);

    expect($descriptions)->toBe([
        'description_ar' => 'عسل سدر يمني طبيعي.',
        'description_en' => 'Natural Yemeni Sidr honey.',
    ]);

    $fake->assertRequest(function (array $requests) {
        /** @var StructuredRequest $request */
        $prompt = $requests[0]->prompt();

        expect($prompt)->toContain('Arabic name: عسل سدر')
            ->toContain('English name: Sidr Honey')
            ->toContain('Country of origin: Yemen')
            ->not->toContain('Storage method');
    });
});

test('missing fields in the model reply come back as empty strings', function () {
    $product = Product::factory()->create();

    Prism::fake([StructuredResponseFake::make()->withStructured(['description_en' => 'Only English.'])]);

    expect(app(AiAssistant::class)->generateProductDescriptions($product))->toBe([
        'description_ar' => '',
        'description_en' => 'Only English.',
    ]);
});
