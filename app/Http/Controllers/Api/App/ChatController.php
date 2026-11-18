<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\ChatRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Traits\HandlesAiErrors;
use App\Http\Traits\ResponseTrait;
use App\Support\Chatbot\ShopChatbot;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    use HandlesAiErrors;
    use ResponseTrait;

    public function __construct(private ShopChatbot $chatbot) {}

    /**
     * One endpoint for the whole chat. Guests can ask about offers and products;
     * with a customer token the bot can also read orders, use the cart and
     * place orders. The client keeps the conversation and sends earlier
     * messages back in `history`.
     */
    public function store(ChatRequest $request): JsonResponse
    {
        return $this->callAi(function () use ($request): JsonResponse {
            $answer = $this->chatbot->reply(
                $request->validated('message'),
                $request->validated('history') ?? [],
                $request->user('sanctum'),
                $request->bearerToken(),
            );

            return $this->successReturn([
                'reply' => $answer['reply'],
                'products' => ProductResource::collection($answer['products']),
                'orders' => OrderResource::collection($answer['orders']),
            ]);
        });
    }
}
