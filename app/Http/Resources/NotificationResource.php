<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => app()->getLocale() === 'ar' ? $this->title_ar : $this->title_en,
            'message' => app()->getLocale() === 'ar' ? $this->message_ar : $this->message_en,
            'is_read' => $this->is_read,
            'order_id' => $this->order_id,
            // 'product_id' => $this->product_id,
            // 'action_label_ar' => $this->action_label_ar,
            // 'action_label_en' => $this->action_label_en,
            'created_at' => $this->created_at,
        ];
    }
}
