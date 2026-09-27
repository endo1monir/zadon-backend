<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_avatar' => api_image($this->customer_avatar),
            'rating' => $this->rating,
            'comment' => $this->comment,
            // 'store_reply' => $this->store_reply,
            // 'store_reply_date' => $this->store_reply_date,
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->order_number),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
