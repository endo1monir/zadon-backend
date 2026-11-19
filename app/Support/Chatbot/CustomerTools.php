<?php

namespace App\Support\Chatbot;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\CartManager;
use App\Support\OrderCheckout;
use Prism\Prism\Tool;

/**
 * Logged-in customer tools for orders and the cart. Cart changes run right
 * away; placing an order lives in ConfirmableActions because it needs a "yes".
 */
class CustomerTools
{
    public function __construct(
        private ApiClient $api,
        private CartManager $cartManager,
        private OrderCheckout $checkout,
    ) {}

    /**
     * @return list<Tool>
     */
    public function tools(ChatSession $session): array
    {
        $user = $session->user;

        return [
            CatalogTools::define('get_my_orders')
                ->withNumberParameter('limit', config('ai.tools.get_my_orders.parameters.limit'), required: false)
                ->withStringParameter('status', config('ai.tools.get_my_orders.parameters.status'), required: false)
                ->using(fn (int|float|null $limit = null, ?string $status = null): string => $this->getMyOrders($session, $user, $limit, $status)),

            CatalogTools::define('get_cart')
                ->using(fn (): string => $this->getCart($session, $user)),

            CatalogTools::define('add_to_cart')
                ->withNumberParameter('product_id', config('ai.tools.add_to_cart.parameters.product_id'))
                ->withNumberParameter('quantity', config('ai.tools.add_to_cart.parameters.quantity'), required: false)
                ->withStringParameter('replace_cart', config('ai.tools.add_to_cart.parameters.replace_cart'), required: false)
                ->using(fn (int|float $product_id, int|float|null $quantity = null, ?string $replace_cart = null): string => $this->addToCart($session, $user, (int) $product_id, (int) ($quantity ?: 1), $replace_cart === 'yes')),

            CatalogTools::define('update_cart_quantity')
                ->withNumberParameter('product_id', config('ai.tools.update_cart_quantity.parameters.product_id'))
                ->withNumberParameter('quantity', config('ai.tools.update_cart_quantity.parameters.quantity'))
                ->using(fn (int|float $product_id, int|float $quantity): string => $this->updateCartQuantity($session, $user, (int) $product_id, (int) $quantity)),

            CatalogTools::define('remove_from_cart')
                ->withNumberParameter('product_id', config('ai.tools.remove_from_cart.parameters.product_id'))
                ->using(fn (int|float $product_id): string => $this->removeFromCart($session, $user, (int) $product_id)),

            CatalogTools::define('clear_cart')
                ->using(fn (): string => $this->clearCart($session, $user)),
        ];
    }

    /**
     * Compact view of a cart for the model; also shows its products as cards.
     *
     * @return array<string, mixed>
     */
    public function describeCart(ChatSession $session, Cart $cart): array
    {
        $cart->loadMissing('items.product', 'store');
        $session->rememberProducts($cart->items->pluck('product_id'));

        return [
            'store' => $session->localized($cart->store?->name_ar, $cart->store?->name_en),
            'items' => $cart->items->map(fn (CartItem $item): array => [
                'product_id' => $item->product_id,
                'name' => $session->localized($item->product?->name_ar, $item->product?->name_en),
                'quantity' => $item->quantity,
                'unit_price_sar' => (float) $item->unit_price,
            ])->all(),
            'totals_sar' => $this->checkout->totals($cart),
        ];
    }

    private function getMyOrders(ChatSession $session, User $user, int|float|null $limit, ?string $status): string
    {
        $limit = max(1, min((int) ($limit ?: config('ai.chat.max_orders')), config('ai.chat.max_orders')));

        $orders = $user->orders()
            ->with('store', 'items')
            ->when(filled($status), fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            return json_encode(['orders' => [], 'note' => 'The customer has no matching orders.']);
        }

        $orders->each(fn (Order $order) => $session->rememberOrder($order->id));

        return json_encode([
            'orders' => $orders->map(fn (Order $order): array => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_name' => trans("messages.order_status_{$order->status}"),
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'total_sar' => (float) $order->total,
                'ordered_at' => $order->created_at?->toDateTimeString(),
                'store' => $session->localized($order->store?->name_ar, $order->store?->name_en),
                'items' => $order->items->map(fn (OrderItem $item): array => [
                    'name' => $session->localized($item->product_name_ar, $item->product_name_en),
                    'quantity' => $item->quantity,
                    'unit_price_sar' => (float) $item->unit_price,
                ])->all(),
            ])->all(),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function getCart(ChatSession $session, User $user): string
    {
        $cart = $this->checkout->activeCart($user);

        if (! $cart || $cart->items->isEmpty()) {
            return json_encode(['cart' => null, 'note' => 'The cart is empty.']);
        }

        return json_encode(['cart' => $this->describeCart($session, $cart)], JSON_UNESCAPED_UNICODE);
    }

    private function addToCart(ChatSession $session, User $user, int $productId, int $quantity, bool $replaceCart): string
    {
        $product = Product::query()->purchasable()->with('store')->find($productId);

        if (! $product) {
            return json_encode(['done' => false, 'error' => 'Product not found or not available. Search again to get a valid product id.']);
        }

        if (! $replaceCart && $this->cartManager->wouldReplaceCart($user, $product)) {
            $currentStore = $this->checkout->activeCart($user)?->store;

            return json_encode([
                'done' => false,
                'needs_customer_confirmation' => true,
                'error' => 'The cart has products from '.$session->localized($currentStore?->name_ar, $currentStore?->name_en)
                    .'. Adding this product will empty that cart. Ask the customer; call again with replace_cart = "yes" only if they agree.',
            ], JSON_UNESCAPED_UNICODE);
        }

        $result = $this->api->call($session, 'POST', 'cart/items', [
            'store_id' => $product->store_id,
            'product_id' => $product->id,
            'quantity' => max(1, $quantity),
        ]);

        return $this->cartResult($session, $user, $result);
    }

    private function updateCartQuantity(ChatSession $session, User $user, int $productId, int $quantity): string
    {
        if ($quantity < 1) {
            return $this->removeFromCart($session, $user, $productId);
        }

        $item = $this->cartItem($user, $productId);

        if (! $item) {
            return json_encode(['done' => false, 'error' => 'This product is not in the cart. Call get_cart to see what is in it.']);
        }

        return $this->cartResult($session, $user, $this->api->call($session, 'PATCH', "cart/items/{$item->id}", ['quantity' => $quantity]));
    }

    private function removeFromCart(ChatSession $session, User $user, int $productId): string
    {
        $item = $this->cartItem($user, $productId);

        if (! $item) {
            return json_encode(['done' => false, 'error' => 'This product is not in the cart. Call get_cart to see what is in it.']);
        }

        return $this->cartResult($session, $user, $this->api->call($session, 'DELETE', "cart/items/{$item->id}"));
    }

    private function clearCart(ChatSession $session, User $user): string
    {
        if (! $this->checkout->activeCart($user)) {
            return json_encode(['done' => true, 'message' => 'The cart was already empty.']);
        }

        $result = $this->api->call($session, 'DELETE', 'cart');

        return $result['ok']
            ? json_encode(['done' => true, 'message' => 'The cart was deleted. It is now empty.'])
            : ApiClient::toolResult($result);
    }

    private function cartItem(User $user, int $productId): ?CartItem
    {
        return $this->checkout->activeCart($user)?->items->firstWhere('product_id', $productId);
    }

    /**
     * @param  array{ok: bool, status: int, message: string, data: mixed, errors: array<string, mixed>}  $result
     */
    private function cartResult(ChatSession $session, User $user, array $result): string
    {
        if (! $result['ok']) {
            return ApiClient::toolResult($result);
        }

        $cart = $this->checkout->activeCart($user);

        return ApiClient::toolResult($result, [
            'cart' => $cart && $cart->items->isNotEmpty() ? $this->describeCart($session, $cart) : 'empty',
        ]);
    }
}
