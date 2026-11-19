<?php

namespace App\Mcp\Tools;

use App\Support\Chatbot\CatalogTools;
use App\Support\Chatbot\ChatSession;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class SearchProductsTool extends CatalogTool
{
    protected function key(): string
    {
        return 'search_products';
    }

    protected function run(Request $request, CatalogTools $catalog, ChatSession $session): string
    {
        $maxPrice = $request->get('max_price');

        return $catalog->searchProducts($session, (string) $request->get('query', ''), is_numeric($maxPrice) ? (float) $maxPrice : null);
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description($this->parameter('query'))->required(),
            'max_price' => $schema->number()->description($this->parameter('max_price')),
        ];
    }
}
