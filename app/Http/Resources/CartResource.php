<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [
                'store' => null,
                'items' => [],
                'items_count' => 0,
                'subtotal' => 0,
                'vat' => 0,
                'vat_rate' => 0.15,
                'delivery_fee' => 0,
                'total' => 0,
            ];
        }

        $subtotal = $this->items->sum(fn ($item) => $item->unit_price * $item->quantity);
        $deliveryFee = $this->store ? (float) $this->store->delivery_fee : 0;
        $vat = round($subtotal * 0.15, 2);

        return [
            'id' => $this->id,
            'store' => StoreResource::make($this->whenLoaded('store')),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items->sum('quantity'),
            'subtotal' => (float) round($subtotal, 2),
            'vat' => $vat,
            'vat_rate' => 0.15,
            'delivery_fee' => $deliveryFee,
            'total' => (float) round($subtotal + $vat + $deliveryFee, 2),
        ];
    }
}