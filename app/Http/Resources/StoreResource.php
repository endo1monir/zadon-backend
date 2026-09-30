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
            'owner_id' => $this->owner_id,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?? $this->name_ar),
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'logo' => api_image($this->logo),
            'cover_image' => api_image($this->cover_image),
            'rating' => (float) $this->rating,
            'rating_count' => $this->rating_count,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'city' => CityResource::make($this->whenLoaded('city')),
            'city_id' => $this->city_id,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'cr_number' => $this->cr_number,
            'vat_number' => $this->vat_number,
            'manager_name' => $this->manager_name,
            'status' => $this->status,
            'status_text' => $this->status ? trans("messages.store_status_{$this->status}") : null,
            'prep_time_min' => $this->prep_time_min.' - '.($this->prep_time_min + 10),
            'delivery_fee' => (float) $this->delivery_fee,
            'min_order' => (float) $this->min_order,
            'delivery_radius_km' => (float) $this->delivery_radius_km,
            'is_verified' => (bool) $this->is_verified,
            'is_open_24_7' => (bool) $this->is_open_24_7,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
