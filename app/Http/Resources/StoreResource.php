<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en,
            'logo' => api_image($this->logo),
            'cover_image' => api_image($this->cover_image),
            'rating' => (float) $this->rating,
            'rating_count' => $this->rating_count,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'city' => $this->city,
            'address' => app()->getLocale() === 'ar' ? $this->address_ar : $this->address_en,
            'status' => $this->status,
            'prep_time_min' => $this->prep_time_min,
            'delivery_fee' => (float) $this->delivery_fee,
            'min_order' => (float) $this->min_order,
            'is_verified' => $this->is_verified,
            'is_open_24_7' => $this->is_open_24_7,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'delivery_radius_km' => (float) $this->delivery_radius_km,
            'is_active' => $this->is_active,
        ];
    }
}
