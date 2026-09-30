<?php

namespace App\Http\Requests\Api\Vendor;

use App\Http\Traits\NormalizesPrepTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateStoreRequest extends FormRequest
{
    use NormalizesPrepTime;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePrepTimes(['store.prep_time_min', 'prep_time_min']);

        $store = $this->input('store');

        if (is_array($store)) {
            $this->merge(Arr::except($store, ['logo', 'cover_image']));
        }
    }

    public function rules(): array
    {
        return [
            'store' => ['sometimes', 'array'],
            'name_ar' => ['sometimes', 'string', 'max:255'],
            'name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
            'category_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'city_id' => ['sometimes', 'integer', 'exists:cities,id'],
            'address' => ['sometimes', 'string', 'max:1000'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'cr_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'vat_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'manager_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'prep_time_min' => ['sometimes', 'integer', 'between:1,240'],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'min_order' => ['sometimes', 'numeric', 'min:0'],
            'delivery_radius_km' => ['sometimes', 'numeric', 'between:1,200'],
            'is_open_24_7' => ['sometimes', 'boolean'],
            'opening_time' => ['sometimes', 'nullable', 'string', 'max:10'],
            'closing_time' => ['sometimes', 'nullable', 'string', 'max:10'],
        ];
    }
}
