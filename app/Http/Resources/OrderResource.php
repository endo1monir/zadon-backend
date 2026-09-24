<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'store' => StoreResource::make($this->whenLoaded('store')),
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'subtotal' => (float) $this->subtotal,
            'vat_amount' => (float) $this->vat_amount,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'city' => $this->city,
            'delivery_address' => $this->delivery_address,
            'notes' => $this->notes,
            'courier_name' => $this->courier_name,
            'courier_phone' => $this->courier_phone,
            'courier_eta_minutes' => $this->courier_eta_minutes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'review' => ReviewResource::make($this->whenLoaded('review')),
            'accepted_at' => $this->accepted_at,
            'prepared_at' => $this->prepared_at,
            'ready_at' => $this->ready_at,
            'out_for_delivery_at' => $this->out_for_delivery_at,
            'delivered_at' => $this->delivered_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}