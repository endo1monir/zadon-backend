<?php

namespace App\Mcp\Tools\Admin;

use App\Models\Review;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[IsIdempotent]
class SetReviewPublishedTool extends AdminAction
{
    protected function key(): string
    {
        return 'set_review_published';
    }

    protected function parameters(JsonSchema $schema): array
    {
        return [
            'review_id' => $schema->integer()->description($this->parameter('review_id'))->required(),
            'published' => $schema->boolean()->description($this->parameter('published'))->required(),
        ];
    }

    protected function prepare(Request $request): string|array
    {
        $review = Review::query()->with('store:id,name_ar,name_en')->find((int) $request->get('review_id'));
        $published = $this->boolean($request->get('published'));

        if (! $review) {
            return 'No review with this id. Use list_reviews to find it.';
        }

        if ($published === null) {
            return 'Send published as true or false.';
        }

        if ((bool) $review->published === $published) {
            return 'Nothing to change: the review is already '.($published ? 'published' : 'hidden').'.';
        }

        $summary = ($published ? 'Publish' : 'Hide')." the {$review->rating}-star review by {$review->customer_name} on {$this->nameOf($review->store)}: \"".str($review->comment)->limit(80).'"';

        return [$summary, function () use ($review, $published): array {
            $review->update(['published' => $published]);

            return ['review_id' => $review->id, 'published' => $published];
        }];
    }
}
