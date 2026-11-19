<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\Admin\GetOrderTool;
use App\Mcp\Tools\Admin\GetOverviewTool;
use App\Mcp\Tools\Admin\ListContactMessagesTool;
use App\Mcp\Tools\Admin\ListReviewsTool;
use App\Mcp\Tools\Admin\ListStoresTool;
use App\Mcp\Tools\Admin\SalesReportTool;
use App\Mcp\Tools\Admin\SearchOrdersTool;
use App\Mcp\Tools\Admin\SearchProductsTool;
use App\Mcp\Tools\Admin\SearchUsersTool;
use App\Mcp\Tools\Admin\SetProductActiveTool;
use App\Mcp\Tools\Admin\SetReviewPublishedTool;
use App\Mcp\Tools\Admin\SetStoreActiveTool;
use App\Mcp\Tools\Admin\SetUserActiveTool;
use App\Mcp\Tools\Admin\UpdateOrderStatusTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\ServerContext;

/**
 * The admin MCP server: a super admin's view of the whole store for
 * assistants such as the Gemini app. Only reachable with an OAuth token
 * issued after an active admin logs in (see routes/ai.php). Texts come
 * from `ai.mcp.admin` in config/ai.php.
 */
#[Name('Zadon Admin')]
#[Version('1.0.0')]
class AdminServer extends Server
{
    protected array $tools = [
        GetOverviewTool::class,
        SalesReportTool::class,
        SearchOrdersTool::class,
        GetOrderTool::class,
        SearchProductsTool::class,
        ListStoresTool::class,
        SearchUsersTool::class,
        ListReviewsTool::class,
        ListContactMessagesTool::class,
        UpdateOrderStatusTool::class,
        SetProductActiveTool::class,
        SetStoreActiveTool::class,
        SetUserActiveTool::class,
        SetReviewPublishedTool::class,
    ];

    public function createContext(): ServerContext
    {
        $this->instructions = config('ai.mcp.admin.instructions');

        return parent::createContext();
    }
}
