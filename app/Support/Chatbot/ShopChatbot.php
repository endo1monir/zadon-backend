<?php

namespace App\Support\Chatbot;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;

/**
 * One chat endpoint for everything: the model reads the customer's message,
 * picks the tools it needs (offers, search, orders, cart, placing an order)
 * and answers from the real data they return. Wording lives in config/ai.php.
 */
class ShopChatbot
{
    public function __construct(
        private CatalogTools $catalogTools,
        private CustomerTools $customerTools,
        private AccountTools $accountTools,
        private ConfirmableActions $confirmableActions,
    ) {}

    /**
     * Answer one customer message.
     *
     * @param  list<array{role: string, content: string}>  $history  Earlier messages, oldest first.
     * @param  string|null  $token  The customer's API token, used when the bot runs actions.
     * @return array{reply: string, products: Collection<int, Product>, orders: Collection<int, Order>}
     */
    public function reply(string $message, array $history = [], ?User $user = null, ?string $token = null): array
    {
        $session = new ChatSession($user, $token);

        $tools = $user === null
            ? $this->catalogTools->tools($session)
            : [
                ...$this->catalogTools->tools($session),
                ...$this->customerTools->tools($session),
                ...$this->accountTools->tools($session),
                ...$this->confirmableActions->tools($session),
            ];

        $response = Prism::text()
            ->using(config('ai.provider'), config('ai.model'))
            ->withSystemPrompt($this->systemPrompt($user))
            ->withMessages($this->messages($message, $history))
            ->withTools($tools)
            ->withMaxSteps(config('ai.chat.max_steps'))
            ->asText();

        return [
            'reply' => trim($response->text),
            'products' => $this->inOrder(Product::query()->with('store', 'category'), $session->productIds()),
            'orders' => $this->inOrder(Order::query()->with('items', 'store', 'paymentMethod', 'review'), $session->orderIds()),
        ];
    }

    private function systemPrompt(?User $user): string
    {
        $account = $user === null
            ? config('ai.prompts.chat.account_guest')
            : strtr(config('ai.prompts.chat.account_customer'), [':name' => $user->name]);

        return strtr(config('ai.prompts.chat.system'), [
            ':account' => $account,
            ':language' => app()->getLocale() === 'ar' ? 'Arabic' : 'English',
        ]);
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return list<UserMessage|AssistantMessage>
     */
    private function messages(string $message, array $history): array
    {
        return collect($history)
            ->take(-config('ai.chat.max_history'))
            ->map(fn (array $entry): UserMessage|AssistantMessage => $entry['role'] === 'assistant'
                ? new AssistantMessage($entry['content'])
                : new UserMessage($entry['content']))
            ->push(new UserMessage($message))
            ->values()
            ->all();
    }

    /**
     * Load models by id, keeping the order the tools found them in.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<int>  $ids
     * @return Collection<int, TModel>
     */
    private function inOrder($query, array $ids): Collection
    {
        if ($ids === []) {
            return new Collection;
        }

        return $query->whereKey($ids)
            ->get()
            ->sortBy(fn ($model): int => (int) array_search($model->getKey(), $ids, true))
            ->values();
    }
}
