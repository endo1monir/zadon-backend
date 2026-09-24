<?php

namespace App\Http\Requests\Api\Vendor;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['preparing', 'ready_for_pickup', 'out_for_delivery', 'delivered'])],
        ];
    }
}