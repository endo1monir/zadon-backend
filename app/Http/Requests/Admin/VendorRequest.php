<?php

namespace App\Http\Requests\Admin;

use App\Http\Traits\NormalizesPrepTime;
use App\Support\AdminOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the same data the vendor register endpoint accepts
 * (POST /api/vendor/auth/register). The `store_` prefixed fields map to the
 * `store` payload of that request.
 */
class VendorRequest extends FormRequest
{
    use NormalizesPrepTime;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizePrepTimes(['store_prep_time_min']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $vendor = $this->route('vendor');

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($vendor),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($vendor),
            ],
            'password' => [$vendor ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['sometimes', 'boolean'],
            'store_name_ar' => ['required', 'string', 'max:255'],
            'store_name_en' => ['nullable', 'string', 'max:255'],
            'store_category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'store_city_id' => ['required', 'integer', 'exists:cities,id'],
            'store_address' => ['required', 'string', 'max:1000'],
            'store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'store_cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'store_phone' => ['nullable', 'string', 'max:20'],
            'store_email' => ['nullable', 'email', 'max:255'],
            'store_cr_number' => ['nullable', 'string', 'max:20'],
            'store_vat_number' => ['nullable', 'string', 'max:20'],
            'store_manager_name' => ['nullable', 'string', 'max:255'],
            'store_prep_time_min' => ['nullable', 'integer', 'between:1,240'],
            'store_delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'store_min_order' => ['nullable', 'numeric', 'min:0'],
            'store_delivery_radius_km' => ['nullable', 'numeric', 'between:1,200'],
            'store_is_open_24_7' => ['sometimes', 'boolean'],
            'store_opening_time' => ['nullable', 'string', 'max:10'],
            'store_closing_time' => ['nullable', 'string', 'max:10'],
            'store_status' => ['sometimes', 'string', Rule::in(array_keys(AdminOptions::storeStatuses()))],
            'store_is_verified' => ['sometimes', 'boolean'],
            'store_is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => __('admin.validation.vendor_phone_required'),
            'password.required' => __('admin.validation.vendor_password_required'),
            'store_name_ar.required' => __('admin.validation.vendor_store_name_ar_required'),
            'store_city_id.required' => __('admin.validation.vendor_store_city_id_required'),
            'store_address.required' => __('admin.validation.vendor_store_address_required'),
        ];
    }

    /**
     * The vendor owner attributes.
     *
     * @return array<string, mixed>
     */
    public function ownerAttributes(): array
    {
        $attributes = [
            'name' => $this->string('name')->toString(),
            'phone' => $this->string('phone')->toString(),
            'email' => $this->input('email'),
            'role' => 'vendor',
            'is_active' => $this->boolean('is_active'),
        ];

        if ($this->filled('password')) {
            $attributes['password'] = $this->string('password')->toString();
        }

        return $attributes;
    }

    /**
     * The store attributes, keyed exactly like the register endpoint payload.
     *
     * @return array<string, mixed>
     */
    public function storeAttributes(): array
    {
        $validated = $this->safe();

        $attributes = [
            'name_ar' => $validated['store_name_ar'],
            'name_en' => $validated['store_name_en'] ?? null,
            'category_id' => $validated['store_category_id'] ?? null,
            'city_id' => $validated['store_city_id'],
            'address' => $validated['store_address'],
            'phone' => $validated['store_phone'] ?? null,
            'email' => $validated['store_email'] ?? null,
            'cr_number' => $validated['store_cr_number'] ?? null,
            'vat_number' => $validated['store_vat_number'] ?? null,
            'manager_name' => $validated['store_manager_name'] ?? null,
            'opening_time' => $validated['store_opening_time'] ?? null,
            'closing_time' => $validated['store_closing_time'] ?? null,
            'status' => $this->input('store_status') ?: 'open',
            'is_open_24_7' => $this->has('store_is_open_24_7') ? $this->boolean('store_is_open_24_7') : false,
            'is_verified' => $this->has('store_is_verified') ? $this->boolean('store_is_verified') : true,
            'is_active' => $this->has('store_is_active') ? $this->boolean('store_is_active') : true,
        ];

        // The operational numbers are not nullable in the schema, so an omitted field keeps
        // the schema default on create and the stored value on update.
        foreach (['prep_time_min', 'delivery_fee', 'min_order', 'delivery_radius_km'] as $field) {
            $value = $this->input('store_'.$field);

            if ($value !== null && $value !== '') {
                $attributes[$field] = $value;
            }
        }

        return $attributes;
    }
}
