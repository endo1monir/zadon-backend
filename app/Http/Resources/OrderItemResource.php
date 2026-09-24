<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name_ar' => $this->product_name_ar,
            'product_name_en' => $this->product_name_en,
            'unit_price' => (float) $this->unit_price,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'image' => api_image($this->image),
            'options' => $this->options,
            'packed' => $this->packed,
            'subtotal' => (float) round($this->unit_price * $this->quantity, 2),
        ];
    }
}