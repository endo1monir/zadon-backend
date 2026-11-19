<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
class ListReviewsTool extends AdminTool
{
    protected function key(): string
    {
        return 'list_reviews';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'max_rating' => $schema->integer()->min(1)->max(5)->description($this->parameter('max_rating')),
            'store_id' => $schema->integer()->description($this->parameter('store_id')),
            'limit' => $schema->integer()->description($this->parameter('limit')),
        ];
    }

    public function handle(Request $request): Response
    {
        $query = Review::query()
            ->with('store:id,name_ar,name_en', 'order:id,order_number')
            ->when(filled($request->get('max_rating')), fn ($query) => $query->where('rating', '<=', (int) $request->get('max_rating')))
            ->when(filled($request->get('store_id')), fn ($query) => $query->where('store_id', (int) $request->get('store_id')));

        $total = (clone $query)->count();

        return $this->json([
            'found' => $total,
            'average_rating' => round((float) (clone $query)->avg('rating'), 2),
            'reviews' => $query->latest('id')->limit($this->limit($request))->get()->map(fn (Review $review): array => [
                'id' => $review->id,
                'rating' => $review->rating,
                'courier_rating' => $review->courier_rating,
                'comment' => $review->comment,
                'customer' => $review->customer_name,
                'store' => $this->nameOf($review->store),
                'order_number' => $review->order?->order_number,
                'published' => (bool) $review->published,
                'store_reply' => $review->store_reply,
                'date' => $review->created_at?->toDateString(),
            ])->all(),
        ]);
    }
}
