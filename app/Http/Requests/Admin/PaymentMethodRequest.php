<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $paymentMethod = $this->route('payment_method');

        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('payment_methods', 'key')->ignore($paymentMethod),
            ],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:svg,jpg,jpeg,png,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.required' => 'A key is required.',
            'key.alpha_dash' => 'The key may only contain letters, numbers, dashes and underscores.',
            'name_en.required' => 'The English name is required.',
        ];
    }
}
