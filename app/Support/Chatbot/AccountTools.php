<?php

namespace App\Support\Chatbot;

use App\Models\User;
use Illuminate\Support\Arr;
use Prism\Prism\Tool;

/**
 * Logged-in customer tools for the account: profile, addresses, wallet,
 * notifications, support messages and order reviews. Each one calls the
 * matching customer API endpoint through ApiClient and runs right away.
 */
class AccountTools
{
    public function __construct(private ApiClient $api) {}

    /**
     * @return list<Tool>
     */
    public function tools(ChatSession $session): array
    {
        $user = $session->user;

        return [
            CatalogTools::define('get_profile')
                ->using(fn (): string => $this->getProfile($session)),

            CatalogTools::define('update_profile')
                ->withStringParameter('name', config('ai.tools.update_profile.parameters.name'), required: false)
                ->withStringParameter('email', config('ai.tools.update_profile.parameters.email'), required: false)
                ->using(fn (?string $name = null, ?string $email = null): string => $this->updateProfile($session, $name, $email)),

            CatalogTools::define('get_addresses')
                ->using(fn (): string => $this->getAddresses($session)),

            CatalogTools::define('add_address')
                ->withStringParameter('full_address', config('ai.tools.add_address.parameters.full_address'))
                ->withStringParameter('title', config('ai.tools.add_address.parameters.title'), required: false)
                ->using(fn (string $full_address, ?string $title = null): string => ApiClient::toolResult(
                    $this->api->call($session, 'POST', 'addresses', array_filter(['full_address' => $full_address, 'title' => $title])),
                )),

            CatalogTools::define('set_default_address')
                ->withNumberParameter('address_id', config('ai.tools.set_default_address.parameters.address_id'))
                ->using(fn (int|float $address_id): string => ApiClient::toolResult(
                    $this->api->call($session, 'POST', 'addresses/'.(int) $address_id.'/default'),
                )),

            CatalogTools::define('get_wallet')
                ->using(fn (): string => $this->getWallet($session)),

            CatalogTools::define('get_notifications')
                ->using(fn (): string => $this->getNotifications($session)),

            CatalogTools::define('contact_support')
                ->withStringParameter('title', config('ai.tools.contact_support.parameters.title'))
                ->withStringParameter('message', config('ai.tools.contact_support.parameters.message'))
                ->using(fn (string $title, string $message): string => ApiClient::toolResult(
                    $this->api->call($session, 'POST', 'contact-us', ['title' => $title, 'message' => $message]),
                )),

            CatalogTools::define('review_order')
                ->withStringParameter('order_number', config('ai.tools.review_order.parameters.order_number'))
                ->withNumberParameter('rating', config('ai.tools.review_order.parameters.rating'))
                ->withStringParameter('comment', config('ai.tools.review_order.parameters.comment'), required: false)
                ->using(fn (string $order_number, int|float $rating, ?string $comment = null): string => $this->reviewOrder($session, $user, $order_number, (int) $rating, $comment)),
        ];
    }

    private function getProfile(ChatSession $session): string
    {
        $result = $this->api->call($session, 'GET', 'auth/me');

        return ApiClient::toolResult($result, [
            'profile' => Arr::only($result['data']['user'] ?? $result['data'] ?? [], ['name', 'phone', 'email', 'city']),
        ]);
    }

    private function updateProfile(ChatSession $session, ?string $name, ?string $email): string
    {
        $changes = array_filter(['name' => $name, 'email' => $email], fn (?string $value): bool => filled($value));

        if ($changes === []) {
            return json_encode(['done' => false, 'error' => 'Nothing to change. Ask the customer for the new name or email. The phone number can only be changed in the app.']);
        }

        $result = $this->api->call($session, 'PATCH', 'profile', $changes);

        return ApiClient::toolResult($result, [
            'profile' => Arr::only($result['data']['user'] ?? [], ['name', 'phone', 'email']),
        ]);
    }

    private function getAddresses(ChatSession $session): string
    {
        $result = $this->api->call($session, 'GET', 'addresses');

        return ApiClient::toolResult($result, [
            'addresses' => collect($result['data']['addresses'] ?? [])
                ->map(fn (array $address): array => Arr::only($address, ['id', 'title', 'full_address', 'is_default']))
                ->all() ?: 'The customer has no saved addresses.',
        ]);
    }

    private function getWallet(ChatSession $session): string
    {
        $wallet = $this->api->call($session, 'GET', 'wallet');
        $transactions = $this->api->call($session, 'GET', 'wallet/transactions');

        return ApiClient::toolResult($wallet, [
            'balance_sar' => $wallet['data']['wallet']['balance'] ?? null,
            'latest_transactions' => collect($transactions['data']['transactions'] ?? [])
                ->take(5)
                ->map(fn (array $transaction): array => Arr::only($transaction, ['type', 'amount', 'balance_after', 'description', 'created_at']))
                ->all(),
            'note' => 'Wallet top-up is only available from the wallet screen in the app.',
        ]);
    }

    private function getNotifications(ChatSession $session): string
    {
        $result = $this->api->call($session, 'GET', 'notifications');

        return ApiClient::toolResult($result, [
            'notifications' => collect($result['data']['notifications'] ?? [])
                ->take(10)
                ->map(fn (array $notification): array => Arr::only($notification, ['title', 'message', 'is_read', 'created_at']))
                ->all() ?: 'No notifications.',
        ]);
    }

    private function reviewOrder(ChatSession $session, User $user, string $orderNumber, int $rating, ?string $comment): string
    {
        $order = $user->orders()->where('order_number', trim($orderNumber))->first();

        if (! $order) {
            return json_encode(['done' => false, 'error' => 'No order with this number belongs to the customer. Call get_my_orders to find the right order number.']);
        }

        $session->rememberOrder($order->id);

        return ApiClient::toolResult(
            $this->api->call($session, 'POST', "orders/{$order->id}/review", array_filter([
                'rating' => $rating,
                'comment' => $comment,
            ], fn (mixed $value): bool => $value !== null && $value !== '')),
        );
    }
}
