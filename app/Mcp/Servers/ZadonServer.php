<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\ListCategoriesTool;
use App\Mcp\Tools\ListOffersTool;
use App\Mcp\Tools\SearchProductsTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\ServerContext;

/**
 * The store's public catalog as an MCP server, for assistants such as the
 * Gemini app. Read-only and needs no login: offers, product search and
 * categories. Texts come from config/ai.php.
 */
#[Name('Zadon')]
#[Version('1.0.0')]
class ZadonServer extends Server
{
    protected array $tools = [
        ListOffersTool::class,
        SearchProductsTool::class,
        ListCategoriesTool::class,
    ];

    public function createContext(): ServerContext
    {
        $this->instructions = config('ai.mcp.instructions');

        return parent::createContext();
    }
}
