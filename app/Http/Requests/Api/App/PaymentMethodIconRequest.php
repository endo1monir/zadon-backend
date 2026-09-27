<?php

namespace App\Http\Requests\Api\App;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodIconRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'icon' => ['required', 'file', 'mimes:svg,jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
