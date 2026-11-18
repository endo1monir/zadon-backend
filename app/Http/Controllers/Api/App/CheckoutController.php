<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\App\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Http\Traits\ResponseTrait;
use App\Support\OrderCheckout;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    use ResponseTrait;

    public function __construct(private OrderCheckout $checkout) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->checkout->activeCart($user);

        if (! $cart || $cart->items->isEmpty()) {
            return $this->failReturn('messages.cart_empty');
        }

        $address = $request->filled('address_id')
            ? $user->addresses()->findOrFail($request->integer('address_id'))
            : $user->addresses()->where('is_default', true)->first();

        $failureReason = $this->checkout->failureReason($user, $cart, $request->payment_method);

        if ($failureReason !== null) {
            return $this->failReturn($failureReason);
        }

        $order = $this->checkout->place($user, $cart, $request->payment_method, $address, $request->notes);

        return $this->successReturn([
            'order' => new OrderResource($order->load('items', 'store', 'paymentMethod', 'review')),
        ], code: 201);
    }
}
