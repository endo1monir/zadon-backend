<?php

use App\Mcp\Servers\ZadonServer;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Servers
|--------------------------------------------------------------------------
| The public catalog for outside assistants (Gemini app, Gemini CLI, Claude,
| ChatGPT...). Read-only, no login. URL: https://your-domain/mcp/zadon
*/

Mcp::web(config('ai.mcp.path'), ZadonServer::class)->middleware('throttle:mcp');
