<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Traits\ResponseTrait;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    use ResponseTrait;

    public function store(StoreReviewRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();

        abort_unless($order->user_id === $user->id, 404);

        if ($order->status !== 'delivered') {
            return $this->failReturn('messages.only_delivered_can_review');
        }

        if ($order->review()->exists()) {
            return $this->failReturn('messages.already_reviewed');
        }

        $review = $order->store->reviews()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'customer_name' => $user->name,
            'customer_avatar' => $user->avatar,
            'rating' => $request->integer('rating'),
            'courier_rating' => $request->input('courier_rating'),
            'comment' => $request->input('comment'),
            'tags' => $request->input('tags'),
            'published' => true,
        ]);

        $order->store->update([
            'rating' => round($order->store->reviews()->where('published', true)->avg('rating'), 2),
            'rating_count' => $order->store->reviews()->where('published', true)->count(),
        ]);

        return $this->successReturn([
            'review' => new ReviewResource($review),
        ], code: 201);
    }
}
