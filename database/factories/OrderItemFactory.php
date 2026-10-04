<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'product_name_ar' => $name,
            'product_name_en' => Str::title($name),
            'unit_price' => fake()->randomFloat(2, 5, 200),
            'quantity' => fake()->numberBetween(1, 6),
            'unit' => fake()->randomElement(['قطعة', 'كيلو', 'علبة']),
            'image' => null,
            'options' => null,
            'packed' => false,
        ];
    }

    public function packed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'packed' => true,
        ]);
    }
}
