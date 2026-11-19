<?php

namespace App\Mcp\Tools;

use App\Support\Chatbot\CatalogTools;
use App\Support\Chatbot\ChatSession;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListOffersTool extends CatalogTool
{
    protected function key(): string
    {
        return 'list_offers';
    }

    protected function run(Request $request, CatalogTools $catalog, ChatSession $session): string
    {
        return $catalog->listOffers($session, $request->get('category'));
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description($this->parameter('category')),
        ];
    }
}
