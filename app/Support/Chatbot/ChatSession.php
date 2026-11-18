<?php

namespace App\Support\Chatbot;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * State for one chatbot reply: who is chatting, and which products and
 * orders the tools showed the model so the API can return them as cards.
 */
class ChatSession
{
    /**
     * Unique id of this reply, used to make sure an order is never prepared
     * and placed in the same reply (the customer must confirm in between).
     */
    public readonly string $turnId;

    /** @var list<int> */
    private array $productIds = [];

    /** @var list<int> */
    private array $orderIds = [];

    /**
     * @param  string|null  $token  The customer's API token, forwarded when the bot calls the API.
     */
    public function __construct(
        public readonly ?User $user = null,
        public readonly ?string $token = null,
    ) {
        $this->turnId = (string) Str::uuid();
    }

    public function isArabic(): bool
    {
        return app()->getLocale() === 'ar';
    }

    /**
     * Pick the Arabic or English value for the current app language.
     */
    public function localized(?string $arabic, ?string $english): ?string
    {
        return $this->isArabic() ? ($arabic ?? $english) : ($english ?? $arabic);
    }

    /**
     * @param  iterable<int>  $ids
     */
    public function rememberProducts(iterable $ids): void
    {
        foreach ($ids as $id) {
            $this->productIds[] = (int) $id;
        }
    }

    public function rememberOrder(int $id): void
    {
        $this->orderIds[] = $id;
    }

    /**
     * @return list<int>
     */
    public function productIds(): array
    {
        return array_values(array_unique($this->productIds));
    }

    /**
     * @return list<int>
     */
    public function orderIds(): array
    {
        return array_values(array_unique($this->orderIds));
    }
}
