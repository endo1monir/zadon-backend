<?php

namespace App\Http\Requests\Api\Vendor;

use App\Http\Traits\NormalizesPrepTime;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRegisterRequest extends FormRequest
{
    use NormalizesPrepTime;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePrepTimes(['store.prep_time_min']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'store' => ['required', 'array'],
            'store.name_ar' => ['required', 'string', 'max:255'],
            'store.name_en' => ['nullable', 'string', 'max:255'],
            'store.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'store.city_id' => ['required', 'integer', 'exists:cities,id'],
            'store.address' => ['required', 'string', 'max:1000'],
            'store.logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'store.cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'store.phone' => ['nullable', 'string', 'max:20'],
            'store.email' => ['nullable', 'email', 'max:255'],
            'store.cr_number' => ['nullable', 'string', 'max:20'],
            'store.vat_number' => ['nullable', 'string', 'max:20'],
            'store.manager_name' => ['nullable', 'string', 'max:255'],
            'store.prep_time_min' => ['nullable', 'integer', 'between:1,240'],
            'store.delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'store.min_order' => ['nullable', 'numeric', 'min:0'],
            'store.delivery_radius_km' => ['nullable', 'numeric', 'between:1,200'],
            'store.is_open_24_7' => ['nullable', 'boolean'],
            'store.opening_time' => ['nullable', 'string', 'max:10'],
            'store.closing_time' => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'store.name_ar' => 'store name',
            'store.name_en' => 'store name',
            'store.city_id' => 'city',
            'store.logo' => 'logo',
            'store.cover_image' => 'cover image',
        ];
    }
}
