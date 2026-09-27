<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\PaymentMethodIconRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Traits\ResponseTrait;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $paymentMethods = PaymentMethod::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $this->successReturn([
            'payment_methods' => PaymentMethodResource::collection($paymentMethods),
        ]);
    }

    public function uploadIcon(PaymentMethodIconRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $icon = $request->file('icon')->store('payment-methods', 'public');

        $paymentMethod->update(['icon' => $icon]);

        return $this->successReturn([
            'payment_method' => new PaymentMethodResource($paymentMethod),
        ], 'messages.payment_method_icon_uploaded');
    }
}
