<?php

namespace App\Support\Chatbot;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Support\OrderCheckout;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Tool;

/**
 * Actions that cannot be undone (placing an order, deleting an address) run
 * in two steps so the customer always says "yes" first:
 *
 * 1. The action tool checks everything and saves it as "pending" for a few
 *    minutes, returning a summary for the model to show.
 * 2. confirm_action runs the pending action, but only in a LATER reply than
 *    the one that prepared it, and (for orders) only if the cart is unchanged.
 */
class ConfirmableActions
{
    public function __construct(
        private ApiClient $api,
        private OrderCheckout $checkout,
        private CustomerTools $customerTools,
    ) {}

    /**
     * @return list<Tool>
     */
    public function tools(ChatSession $session): array
    {
        $user = $session->user;

        return [
            CatalogTools::define('place_order')
                ->withStringParameter('payment_method', config('ai.tools.place_order.parameters.payment_method'))
                ->using(fn (string $payment_method): string => $this->preparePlaceOrder($session, $user, $payment_method)),

            CatalogTools::define('delete_address')
                ->withNumberParameter('address_id', config('ai.tools.delete_address.parameters.address_id'))
                ->using(fn (int|float $address_id): string => $this->prepareDeleteAddress($session, $user, (int) $address_id)),

            CatalogTools::define('confirm_action')
                ->using(fn (): string => $this->confirm($session, $user)),

            CatalogTools::define('cancel_action')
                ->using(fn (): string => $this->cancel($user)),
        ];
    }

    public static function pendingKey(User $user): string
    {
        return "chatbot:pending-action:{$user->id}";
    }

    private function preparePlaceOrder(ChatSession $session, User $user, string $paymentMethod): string
    {
        $paymentMethod = strtolower(trim($paymentMethod));

        $isAllowed = in_array($paymentMethod, config('ai.chat.payment_methods'), true)
            && PaymentMethod::query()->active()->where('key', $paymentMethod)->exists();

        if (! $isAllowed) {
            return json_encode(['prepared' => false, 'error' => 'This payment method cannot be used in chat. Offer cash on delivery or the Zadon wallet, or ask the customer to check out from the cart screen.']);
        }

        $cart = $this->checkout->activeCart($user);
        $failureReason = $this->checkout->failureReason($user, $cart, $paymentMethod);

        if ($failureReason !== null) {
            return json_encode(['prepared' => false, 'error' => $failureReason], JSON_UNESCAPED_UNICODE);
        }

        $address = $user->addresses()->orderByDesc('is_default')->latest('id')->first();

        if (! $address) {
            return json_encode(['prepared' => false, 'error' => 'The customer has no saved delivery address. Offer to add one with add_address first.']);
        }

        $paymentMethodModel = PaymentMethod::query()->where('key', $paymentMethod)->first();

        $this->savePending($session, $user, [
            'action' => 'place_order',
            'payment_method' => $paymentMethod,
            'address_id' => $address->id,
            'cart_id' => $cart->id,
            'cart_fingerprint' => $this->fingerprint($cart),
        ]);

        return json_encode([
            'prepared' => true,
            'summary' => [
                ...$this->customerTools->describeCart($session, $cart),
                'delivery_address' => $address->full_address,
                'payment_method' => $session->localized($paymentMethodModel?->name_ar, $paymentMethodModel?->name_en),
            ],
            'next_step' => 'Show this summary and ask the customer to confirm. Call confirm_action only after they say yes in their next message.',
        ], JSON_UNESCAPED_UNICODE);
    }

    private function prepareDeleteAddress(ChatSession $session, User $user, int $addressId): string
    {
        $address = $user->addresses()->find($addressId);

        if (! $address) {
            return json_encode(['prepared' => false, 'error' => 'No such address. Call get_addresses to find the right id.']);
        }

        $this->savePending($session, $user, ['action' => 'delete_address', 'address_id' => $address->id]);

        return json_encode([
            'prepared' => true,
            'summary' => ['delete_address' => trim(($address->title ? "{$address->title}: " : '').$address->full_address)],
            'next_step' => 'Ask the customer to confirm deleting this address. Call confirm_action only after they say yes in their next message.',
        ], JSON_UNESCAPED_UNICODE);
    }

    private function confirm(ChatSession $session, User $user): string
    {
        $pending = Cache::get(self::pendingKey($user));

        if (! $pending) {
            return json_encode(['done' => false, 'error' => 'There is nothing waiting for confirmation, or it expired. Prepare the action again and show the summary.']);
        }

        if ($pending['prepared_in'] === $session->turnId) {
            return json_encode(['done' => false, 'error' => 'The customer has not confirmed yet. Show the summary and wait for their reply before calling confirm_action.']);
        }

        return match ($pending['action']) {
            'place_order' => $this->placeOrder($session, $user, $pending),
            'delete_address' => $this->deleteAddress($session, $user, $pending),
            default => json_encode(['done' => false, 'error' => 'Unknown pending action.']),
        };
    }

    private function cancel(User $user): string
    {
        Cache::forget(self::pendingKey($user));

        return json_encode(['done' => true, 'message' => 'The pending action was cancelled. Nothing was changed.']);
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    private function placeOrder(ChatSession $session, User $user, array $pending): string
    {
        $cart = $this->checkout->activeCart($user);

        if (! $cart || $cart->id !== $pending['cart_id'] || $this->fingerprint($cart) !== $pending['cart_fingerprint']) {
            Cache::forget(self::pendingKey($user));

            return json_encode(['done' => false, 'error' => 'The cart changed since the summary. Call place_order again and ask the customer to confirm the new summary.']);
        }

        if (! Cache::pull(self::pendingKey($user))) {
            return json_encode(['done' => false, 'error' => 'This order was already placed.']);
        }

        $result = $this->api->call($session, 'POST', 'checkout', [
            'payment_method' => $pending['payment_method'],
            'address_id' => $pending['address_id'],
        ]);

        if (! $result['ok']) {
            return ApiClient::toolResult($result);
        }

        $order = $result['data']['order'] ?? [];
        $session->rememberOrder((int) ($order['id'] ?? 0));

        return ApiClient::toolResult($result, [
            'order_number' => $order['order_number'] ?? null,
            'total_sar' => $order['total'] ?? null,
            'payment_status' => $order['payment_status'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $pending
     */
    private function deleteAddress(ChatSession $session, User $user, array $pending): string
    {
        Cache::forget(self::pendingKey($user));

        return ApiClient::toolResult($this->api->call($session, 'DELETE', "addresses/{$pending['address_id']}"));
    }

    /**
     * @param  array<string, mixed>  $action
     */
    private function savePending(ChatSession $session, User $user, array $action): void
    {
        Cache::put(
            self::pendingKey($user),
            [...$action, 'prepared_in' => $session->turnId],
            now()->addMinutes(config('ai.chat.confirmation_minutes')),
        );
    }

    /**
     * Changes whenever an item, quantity or price in the cart changes.
     */
    private function fingerprint(Cart $cart): string
    {
        return sha1($cart->items()
            ->orderBy('id')
            ->get(['product_id', 'quantity', 'unit_price'])
            ->map(fn (CartItem $item): string => "{$item->product_id}:{$item->quantity}:{$item->unit_price}")
            ->implode('|'));
    }
}
