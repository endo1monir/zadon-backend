<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => api_image($this->avatar),
            'city' => $this->city ? new CityResource($this->city) : null,
            'role' => $this->role,
            'is_completed' => (bool) $this->is_completed,
            'wallet' => $this->whenLoaded('wallet', fn () => new WalletResource($this->wallet)),
            'created_at' => $this->created_at,
        ];
    }
}
