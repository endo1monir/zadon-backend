<?php

use App\Mcp\Servers\AdminServer;
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
use App\Mcp\Tools\Admin\SetUserActiveTool;
use App\Mcp\Tools\Admin\UpdateOrderStatusTool;
use App\Models\ContactMessage;
use App\Models\McpAdmin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Mcp\Server\Testing\TestResponse;
use Laravel\Passport\Passport;

uses(RefreshDatabase::class);

/**
 * The admin MCP server: OAuth-protected, every tool reads the live database,
 * and changes need a summary first and a one-time code second.
 */
beforeEach(function () {
    TestResponse::macro('data', function (): array {
        return json_decode($this->response->toArray()['result']['content'][0]['text'], true);
    });

    $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $this->mcpAdmin = McpAdmin::find($this->admin->id);
    $this->store = Store::factory()->create(['name_en' => 'Zadon Pharmacy']);
});

function adminTool(string $tool, array $arguments = []): TestResponse
{
    return AdminServer::actingAs(test()->mcpAdmin, 'mcp')->tool($tool, $arguments);
}

function order(array $attributes = []): Order
{
    return Order::factory()->create(['store_id' => test()->store->id, 'status' => 'delivered', 'total' => 100, ...$attributes]);
}

// Access

test('the admin server refuses requests without a token and points to the OAuth metadata', function () {
    $this->postJson('/'.config('ai.mcp.admin.path'), ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate');

    expect($this->getJson('/.well-known/oauth-authorization-server')->assertOk()->json())
        ->toHaveKeys(['authorization_endpoint', 'token_endpoint', 'registration_endpoint'])
        ->and($this->getJson('/.well-known/oauth-authorization-server')->json('code_challenge_methods_supported'))->toBe(['S256']);
});

test('an assistant like Gemini can register itself as an OAuth client', function () {
    $this->postJson('/oauth/register', [
        'client_name' => 'Gemini',
        'redirect_uris' => ['https://oauth-redirect.googleusercontent.com/r/zadon'],
    ])->assertCreated()->assertJsonStructure(['client_id']);
});

test('the full OAuth flow gives a logged-in admin a token that opens the admin server', function () {
    $redirect = 'https://oauth-redirect.googleusercontent.com/r/zadon';
    $clientId = $this->postJson('/oauth/register', ['client_name' => 'Gemini', 'redirect_uris' => [$redirect]])->json('client_id');

    $verifier = Str::random(64);
    $query = http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirect,
        'response_type' => 'code',
        'scope' => 'mcp:use',
        'state' => 'xyz',
        'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
        'code_challenge_method' => 'S256',
    ]);

    // A guest is sent to the admin login page first.
    $this->get("/oauth/authorize?{$query}")->assertRedirect(route('admin.login'));

    // A logged-in admin sees the approval page, then approves.
    $this->actingAs($this->admin)->get("/oauth/authorize?{$query}")->assertOk()->assertSee('Gemini');

    $approved = $this->post('/oauth/authorize', [
        'state' => 'xyz',
        'client_id' => $clientId,
        'auth_token' => session('authToken'),
    ]);

    parse_str(parse_url($approved->headers->get('Location'), PHP_URL_QUERY), $params);

    $token = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => $clientId,
        'redirect_uri' => $redirect,
        'code_verifier' => $verifier,
        'code' => $params['code'],
    ])->assertOk()->json();

    expect($token)->toHaveKeys(['access_token', 'refresh_token']);

    app('auth')->forgetGuards();

    $this->withToken($token['access_token'])
        ->postJson('/'.config('ai.mcp.admin.path'), ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])
        ->assertOk()
        ->assertJsonPath('result.tools.0.name', 'get_overview');
});

test('only active admins can hold an admin MCP token', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $vendor = User::factory()->create(['role' => 'vendor']);
    $blockedAdmin = User::factory()->create(['role' => 'admin', 'is_active' => false]);

    expect(McpAdmin::find($this->admin->id))->not->toBeNull()
        ->and(McpAdmin::find($customer->id))->toBeNull()
        ->and(McpAdmin::find($vendor->id))->toBeNull()
        ->and(McpAdmin::find($blockedAdmin->id))->toBeNull();
});

test('a logged-in admin gets every admin tool over HTTP', function () {
    Passport::actingAs($this->mcpAdmin, [], 'mcp');

    $tools = $this->postJson('/'.config('ai.mcp.admin.path'), ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])
        ->assertOk()
        ->json('result.tools');

    expect(collect($tools)->pluck('name')->all())->toBe([
        'get_overview', 'sales_report', 'search_orders', 'get_order', 'search_products', 'list_stores',
        'search_users', 'list_reviews', 'list_contact_messages',
        'update_order_status', 'set_product_active', 'set_store_active', 'set_user_active', 'set_review_published',
    ]);
});

// Reading

test('the overview counts revenue without cancelled orders', function () {
    order(['total' => 100]);
    order(['total' => 50, 'status' => 'new']);
    order(['total' => 999, 'status' => 'cancelled']);

    $data = adminTool(GetOverviewTool::class)->assertOk()->data();

    expect($data['totals']['orders'])->toBe(3)
        ->and($data['totals']['revenue_sar'])->toBe(150.0)
        ->and($data['new_orders_waiting'])->toBe(1)
        ->and($data['today']['revenue_sar'])->toBe(150.0);
});

test('the sales report covers the period, breaks down by store and lists top products', function () {
    $other = Store::factory()->create(['name_en' => 'Nakheel Pharmacy']);
    $inPeriod = order(['total' => 200, 'created_at' => '2026-10-10 12:00:00']);
    order(['total' => 100, 'store_id' => $other->id, 'created_at' => '2026-10-11 12:00:00']);
    order(['total' => 500, 'created_at' => '2026-09-01 12:00:00']);
    order(['total' => 700, 'status' => 'cancelled', 'created_at' => '2026-10-12 12:00:00']);
    OrderItem::factory()->create(['order_id' => $inPeriod->id, 'product_name_en' => 'Sidr Honey', 'quantity' => 3, 'unit_price' => 50]);

    $data = adminTool(SalesReportTool::class, ['from' => '2026-10-01', 'to' => '2026-10-31', 'group_by' => 'store'])->assertOk()->data();

    expect($data['orders'])->toBe(2)
        ->and($data['revenue_sar'])->toBe(300.0)
        ->and($data['average_order_sar'])->toBe(150.0)
        ->and($data['cancelled_orders'])->toBe(1)
        ->and($data['breakdown'][0])->toMatchArray(['store_id' => $this->store->id, 'revenue_sar' => 200.0])
        ->and($data['top_products'][0])->toMatchArray(['quantity' => 3, 'revenue_sar' => 150.0]);
});

test('orders can be searched by phone and opened in full', function () {
    $order = order(['order_number' => 'ZD-777', 'customer_name' => 'Hager', 'customer_phone' => '0551234567', 'status' => 'new']);
    order(['order_number' => 'ZD-888', 'customer_phone' => '0559999999']);

    $found = adminTool(SearchOrdersTool::class, ['search' => '0551234567'])->data();

    expect($found['found'])->toBe(1)
        ->and($found['orders'][0]['order_number'])->toBe('ZD-777');

    $details = adminTool(GetOrderTool::class, ['order_number' => 'ZD-777'])->data();

    expect($details['customer']['phone'])->toBe('0551234567')
        ->and($details['can_move_to'])->toBe($order->allowedStatusTransitions());
});

test('product search finds low stock across active and inactive products', function () {
    Product::factory()->create(['name_en' => 'Almost Gone', 'stock' => 2, 'min_stock_alert' => 5, 'is_active' => false]);
    Product::factory()->create(['name_en' => 'Sold Out', 'stock' => 0, 'min_stock_alert' => 0]);
    Product::factory()->create(['name_en' => 'Plenty', 'stock' => 100, 'min_stock_alert' => 5]);

    adminTool(SearchProductsTool::class, ['low_stock' => true])
        ->assertSee(['Almost Gone', 'Sold Out'])
        ->assertDontSee('Plenty');
});

test('stores come with their owner, orders and revenue', function () {
    order(['total' => 300]);

    $data = adminTool(ListStoresTool::class, ['search' => 'Zadon'])->data();

    expect($data['stores'][0])->toMatchArray(['id' => $this->store->id, 'orders' => 1, 'revenue_sar' => 300.0]);
});

test('top spenders are listed with their totals', function () {
    $big = User::factory()->create(['role' => 'customer', 'name' => 'Big Spender']);
    $small = User::factory()->create(['role' => 'customer', 'name' => 'Small Spender']);
    order(['user_id' => $big->id, 'total' => 900]);
    order(['user_id' => $small->id, 'total' => 50]);

    $data = adminTool(SearchUsersTool::class, ['sort' => 'top_spenders'])->data();

    expect($data['users'][0])->toMatchArray(['name' => 'Big Spender', 'total_spent_sar' => 900.0, 'orders' => 1]);
});

test('bad reviews and contact messages can be read', function () {
    Review::factory()->create(['store_id' => $this->store->id, 'rating' => 1, 'comment' => 'Late delivery']);
    Review::factory()->create(['store_id' => $this->store->id, 'rating' => 5, 'comment' => 'Great']);
    ContactMessage::factory()->create(['title' => 'Refund please']);

    adminTool(ListReviewsTool::class, ['max_rating' => 2])->assertSee('Late delivery')->assertDontSee('Great');
    adminTool(ListContactMessagesTool::class)->assertSee('Refund please');
});

// Changing

test('changing an order status needs a summary first and the code second', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $order = order(['order_number' => 'ZD-500', 'status' => 'new', 'user_id' => $customer->id]);

    $first = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-500', 'status' => 'preparing'])->data();

    expect($first['done'])->toBeFalse()
        ->and($first['needs_confirmation'])->toBeTrue()
        ->and($first['summary'])->toContain('new → preparing')
        ->and($order->fresh()->status)->toBe('new');

    $second = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-500', 'status' => 'preparing', 'confirmation_code' => $first['confirmation_code']])->data();

    expect($second['done'])->toBeTrue()
        ->and($order->fresh()->status)->toBe('preparing')
        ->and($customer->notifications()->count())->toBe(1);

    // The code works once only.
    $again = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-500', 'status' => 'preparing', 'confirmation_code' => $first['confirmation_code']])->data();

    expect($again['done'])->toBeFalse();
});

test('a confirmation code cannot be used for different arguments', function () {
    $order = order(['order_number' => 'ZD-501', 'status' => 'new']);

    $code = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-501', 'status' => 'preparing'])->data()['confirmation_code'];
    $result = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-501', 'status' => 'cancelled', 'confirmation_code' => $code])->data();

    expect($result['done'])->toBeFalse()
        ->and($order->fresh()->status)->toBe('new');
});

test('an order cannot jump to a status it is not allowed to reach', function () {
    order(['order_number' => 'ZD-502', 'status' => 'delivered']);

    $result = adminTool(UpdateOrderStatusTool::class, ['order_number' => 'ZD-502', 'status' => 'preparing'])->data();

    expect($result['done'])->toBeFalse()
        ->and($result['error'])->toContain('cannot move')
        ->and($result)->not->toHaveKey('confirmation_code');
});

test('a product can be deactivated after confirmation', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $code = adminTool(SetProductActiveTool::class, ['product_id' => $product->id, 'active' => false])->data()['confirmation_code'];

    expect($product->fresh()->is_active)->toBeTrue();

    adminTool(SetProductActiveTool::class, ['product_id' => $product->id, 'active' => false, 'confirmation_code' => $code])
        ->assertOk();

    expect($product->fresh()->is_active)->toBeFalse();
});

test('admin accounts cannot be blocked from the assistant', function () {
    $otherAdmin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $result = adminTool(SetUserActiveTool::class, ['user_id' => $otherAdmin->id, 'active' => false])->data();

    expect($result['done'])->toBeFalse()
        ->and($otherAdmin->fresh()->is_active)->toBeTrue();
});
