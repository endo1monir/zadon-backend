<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'name' => app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en,
            'icon' => api_image($this->icon),
        ];
    }
}
