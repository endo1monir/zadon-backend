<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VendorRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
            'store.city' => ['required', 'string', 'max:255'],
            'store.address_ar' => ['required', 'string', 'max:1000'],
            'store.phone' => ['nullable', 'string', 'max:20'],
            'store.cr_number' => ['nullable', 'string', 'max:20'],
            'store.vat_number' => ['nullable', 'string', 'max:20'],
            'store.manager_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}