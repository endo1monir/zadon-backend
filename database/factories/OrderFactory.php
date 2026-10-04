<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 20, 400);
        $vat = round($subtotal * 0.15, 2);
        $deliveryFee = fake()->randomElement([0, 10, 15, 20]);

        return [
            'order_number' => 'ORD-'.Str::upper(Str::random(8)),
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'status' => 'new',
            'payment_method' => fake()->randomElement(['cash', 'card', 'apple_pay', 'wallet']),
            'payment_status' => fake()->randomElement(['pending', 'paid']),
            'subtotal' => $subtotal,
            'vat_amount' => $vat,
            'delivery_fee' => $deliveryFee,
            'total' => round($subtotal + $vat + $deliveryFee, 2),
            'delivery_address' => 'حي النخيل، شارع الأمير سلطان',
            'city' => 'الرياض',
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->numerify('05########'),
            'notes' => null,
            'courier_name' => null,
            'courier_phone' => null,
            'courier_eta_minutes' => null,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => $status,
        ]);
    }

    public function cancelled(): static
    {
        return $this->status('cancelled');
    }

    public function delivered(): static
    {
        return $this->status('delivered');
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }
}
