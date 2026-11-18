<?php

namespace App\Support;

use App\Models\Product;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

/**
 * Single entry point for one-off AI calls. Provider, model and all prompt
 * wording come from config/ai.php.
 */
class AiAssistant
{
    /**
     * Generate free-form text from a prompt.
     */
    public function generateText(string $prompt, ?string $systemPrompt = null): string
    {
        $request = Prism::text()
            ->using(config('ai.provider'), config('ai.model'))
            ->withPrompt($prompt);

        if ($systemPrompt !== null) {
            $request->withSystemPrompt($systemPrompt);
        }

        return $request->asText()->text;
    }

    /**
     * Write Arabic and English marketing descriptions for a product.
     *
     * @return array{description_ar: string, description_en: string}
     */
    public function generateProductDescriptions(Product $product): array
    {
        $prompts = config('ai.prompts.product_descriptions');

        $schema = new ObjectSchema(
            name: 'product_descriptions',
            description: 'Marketing descriptions for a store product',
            properties: collect($prompts['fields'])
                ->map(fn (string $description, string $field): StringSchema => new StringSchema($field, $description))
                ->values()
                ->all(),
            requiredFields: array_keys($prompts['fields']),
        );

        $productDetails = collect([
            'Arabic name' => $product->name_ar,
            'English name' => $product->name_en,
            'Unit' => $product->unit_en ?? $product->unit_ar,
            'Country of origin' => $product->country_of_origin,
            'Storage method' => $product->storage_method,
        ])->filter()->map(fn (string $value, string $label): string => "{$label}: {$value}")->implode("\n");

        $structured = Prism::structured()
            ->using(config('ai.provider'), config('ai.model'))
            ->withSchema($schema)
            ->withSystemPrompt($prompts['system'])
            ->withPrompt(strtr($prompts['user'], [':details' => $productDetails]))
            ->asStructured()
            ->structured;

        return [
            'description_ar' => (string) ($structured['description_ar'] ?? ''),
            'description_en' => (string) ($structured['description_en'] ?? ''),
        ];
    }
}
