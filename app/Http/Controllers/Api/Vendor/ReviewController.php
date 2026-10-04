<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Requests\Api\Vendor\StoreReviewReplyRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $reviews = $this->managedStore()->reviews()
            ->with('order')
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->integer('rating')))
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('customer_name', 'like', "%{$request->string('search')}%")
                    ->orWhere('comment', 'like', "%{$request->string('search')}%")
                    ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', "%{$request->string('search')}%")))
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return $this->successReturn([
            'reviews' => ReviewResource::collection($reviews),
            'summary' => [
                'average' => (float) round($this->managedStore()->rating, 2),
                'count' => $this->managedStore()->rating_count,
                'distribution' => [
                    5 => $this->managedStore()->reviews()->where('rating', 5)->count(),
                    4 => $this->managedStore()->reviews()->where('rating', 4)->count(),
                    3 => $this->managedStore()->reviews()->where('rating', 3)->count(),
                    2 => $this->managedStore()->reviews()->where('rating', 2)->count(),
                    1 => $this->managedStore()->reviews()->where('rating', 1)->count(),
                ],
            ],
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    public function reply(StoreReviewReplyRequest $request, Review $review): JsonResponse
    {
        $review = $this->managedStore()->reviews()->findOrFail($review->id);

        $review->update([
            'store_reply' => $request->reply,
            'store_reply_date' => now(),
        ]);

        return $this->successReturn([
            'review' => new ReviewResource($review),
        ]);
    }
}
