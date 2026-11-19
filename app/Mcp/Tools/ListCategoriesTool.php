<?php

namespace App\Mcp\Tools;

use App\Support\Chatbot\CatalogTools;
use App\Support\Chatbot\ChatSession;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListCategoriesTool extends CatalogTool
{
    protected function key(): string
    {
        return 'list_categories';
    }

    protected function run(Request $request, CatalogTools $catalog, ChatSession $session): string
    {
        return $catalog->listCategories($request->get('parent_only') !== 'no');
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'parent_only' => $schema->string()->enum(['yes', 'no'])->description($this->parameter('parent_only')),
        ];
    }
}
