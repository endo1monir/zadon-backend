<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'category_id' => $this->category_id,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en,
            'price' => (float) $this->price,
            'original_price' => $this->original_price !== null ? (float) $this->original_price : null,
            'stock' => $this->stock,
            'unit' => app()->getLocale() === 'ar' ? $this->unit_ar : $this->unit_en,
            'image' => api_image($this->image),
            'is_prescription_required' => $this->is_prescription_required,
            'storage_temp' => $this->storage_temp,
            'country_of_origin' => $this->country_of_origin,
            'expiry_date' => $this->expiry_date,
            'sales_count' => $this->sales_count,
            'description' => app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en,
            'options' => $this->options,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'store' => StoreResource::make($this->whenLoaded('store')),
        ];
    }
}