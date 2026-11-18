<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\ChatRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Traits\HandlesAiErrors;
use App\Http\Traits\ResponseTrait;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\Chatbot\ShopChatbot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    use HandlesAiErrors;
    use ResponseTrait;

    public function __construct(private ShopChatbot $chatbot) {}

    /**
     * One endpoint for the whole chat, no login needed. Guests can ask about
     * offers and products; with a customer token the bot can also read orders,
     * use the cart and place orders.
     *
     * The first message starts a conversation and the response returns its
     * `conversation_id`. Send that id with the next messages so the bot
     * remembers the chat, and use it with the history endpoint.
     */
    public function store(ChatRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $conversation = null;

        if (filled($request->validated('conversation_id'))) {
            $conversation = $this->findConversation($request->validated('conversation_id'), $user);

            if (! $conversation) {
                return $this->failReturn('messages.chat_not_found', null, 404);
            }
        }

        return $this->callAi(function () use ($request, $user, $conversation): JsonResponse {
            $message = $request->validated('message');

            $answer = $this->chatbot->reply(
                $message,
                $conversation ? $this->recentHistory($conversation) : [],
                $user,
                $request->bearerToken(),
            );

            $conversation = DB::transaction(function () use ($conversation, $user, $message, $answer): ChatConversation {
                $conversation ??= ChatConversation::query()->create(['user_id' => $user?->id]);

                // A guest who logs in mid-chat keeps the conversation, and it becomes theirs.
                if ($conversation->user_id === null && $user !== null) {
                    $conversation->update(['user_id' => $user->id]);
                }

                $conversation->messages()->create(['role' => 'user', 'content' => $message]);
                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $answer['reply'],
                    'product_ids' => $answer['products']->modelKeys() ?: null,
                    'order_ids' => $answer['orders']->modelKeys() ?: null,
                ]);
                $conversation->touch();

                return $conversation;
            });

            return $this->successReturn([
                'conversation_id' => $conversation->id,
                'reply' => $answer['reply'],
                'products' => ProductResource::collection($answer['products']),
                'orders' => OrderResource::collection($answer['orders']),
            ]);
        });
    }

    /**
     * Every message of one conversation, oldest first, with the product and
     * order cards that were shown with the bot's answers.
     */
    public function history(Request $request, string $conversationId): JsonResponse
    {
        $conversation = $this->findConversation($conversationId, $request->user('sanctum'));

        if (! $conversation) {
            return $this->failReturn('messages.chat_not_found', null, 404);
        }

        $messages = $conversation->messages()->get();

        $products = Product::query()
            ->with('store', 'category')
            ->whereKey($messages->pluck('product_ids')->flatten()->filter()->unique()->values()->all())
            ->get()
            ->keyBy('id');

        $orders = $conversation->user_id === null
            ? collect()
            : Order::query()
                ->with('items', 'store', 'paymentMethod', 'review')
                ->where('user_id', $conversation->user_id)
                ->whereKey($messages->pluck('order_ids')->flatten()->filter()->unique()->values()->all())
                ->get()
                ->keyBy('id');

        return $this->successReturn([
            'conversation_id' => $conversation->id,
            'messages' => $messages->map(fn (ChatMessage $message): array => [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'products' => ProductResource::collection(collect($message->product_ids ?? [])->map(fn (int $id) => $products->get($id))->filter()->values()),
                'orders' => OrderResource::collection(collect($message->order_ids ?? [])->map(fn (int $id) => $orders->get($id))->filter()->values()),
                'created_at' => $message->created_at?->toIso8601String(),
            ])->all(),
        ]);
    }

    private function findConversation(string $conversationId, ?User $user): ?ChatConversation
    {
        if (! Str::isUuid($conversationId)) {
            return null;
        }

        $conversation = ChatConversation::query()->find($conversationId);

        return $conversation?->isAccessibleBy($user) ? $conversation : null;
    }

    /**
     * The last messages of the conversation, oldest first, sent to the model
     * so it remembers what was said.
     *
     * @return list<array{role: string, content: string}>
     */
    private function recentHistory(ChatConversation $conversation): array
    {
        return $conversation->messages()
            ->reorder('id', 'desc')
            ->limit(config('ai.chat.max_history'))
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn (ChatMessage $message): array => ['role' => $message->role, 'content' => $message->content])
            ->values()
            ->all();
    }
}
