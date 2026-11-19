<?php

use App\Mcp\Servers\AdminServer;
use App\Mcp\Servers\ZadonServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;

/*
|--------------------------------------------------------------------------
| MCP Servers
|--------------------------------------------------------------------------
| For outside assistants (Gemini app, Gemini CLI, Claude, ChatGPT...).
|
| Public catalog: read-only, no login.      https://your-domain/mcp/zadon
| Admin:          OAuth login as an admin.   https://your-domain/mcp/admin
|
| oauthRoutes() publishes the OAuth discovery documents and lets assistants
| register themselves; the login and approval pages come from Passport.
*/

Route::middleware('throttle:30,1')->group(fn () => Mcp::oauthRoutes());

Mcp::web(config('ai.mcp.path'), ZadonServer::class)->middleware('throttle:mcp');

Mcp::web(config('ai.mcp.admin.path'), AdminServer::class)->middleware(['auth:mcp', 'throttle:mcp']);
