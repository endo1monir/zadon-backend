<?php

namespace App\Http\Requests\Api\App;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['sometimes', 'exists:addresses,id'],
            'payment_method' => ['required', Rule::exists('payment_methods', 'key')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
