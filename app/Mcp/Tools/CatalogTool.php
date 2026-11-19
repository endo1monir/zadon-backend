<?php

namespace App\Mcp\Tools;

use App\Support\Chatbot\CatalogTools;
use App\Support\Chatbot\ChatSession;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Illuminate\Support\Traits\Localizable;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

/**
 * Base for the MCP catalog tools. Each one runs the same code as the chatbot
 * tool of the same name (CatalogTools) and takes its name and description
 * from config/ai.php, so the bot and the MCP server never drift apart.
 * Each tool is read-only; mark it with #[IsReadOnly] (attributes are not inherited).
 */
abstract class CatalogTool extends Tool
{
    use Localizable;

    /**
     * The tool's key under `ai.tools` in config/ai.php.
     */
    abstract protected function key(): string;

    /**
     * Run the catalog lookup and return its JSON.
     */
    abstract protected function run(Request $request, CatalogTools $catalog, ChatSession $session): string;

    public function name(): string
    {
        return $this->key();
    }

    public function description(): string
    {
        return config("ai.tools.{$this->key()}.description");
    }

    public function handle(Request $request, CatalogTools $catalog): Response
    {
        $language = $request->get('language') === 'en' ? 'en' : 'ar';

        return Response::text(
            $this->withLocale($language, fn (): string => $this->run($request, $catalog, new ChatSession))
        );
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            ...$this->parameters($schema),
            'language' => $schema->string()->enum(['ar', 'en'])->description(config('ai.mcp.language')),
        ];
    }

    /**
     * The tool's own parameters, described from config/ai.php.
     *
     * @return array<string, Type>
     */
    protected function parameters(JsonSchema $schema): array
    {
        return [];
    }

    protected function parameter(string $name): string
    {
        return config("ai.tools.{$this->key()}.parameters.{$name}");
    }
}
