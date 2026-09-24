<?php

namespace App\Http\Requests\Api\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delta' => ['required', 'integer', 'not_in:0'],
            'type' => ['sometimes', Rule::in(['sale', 'restock', 'correction', 'waste'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}