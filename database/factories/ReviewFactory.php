<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'user_id' => User::factory(),
            'order_id' => null,
            'customer_name' => fake()->name(),
            'customer_avatar' => null,
            'rating' => fake()->numberBetween(1, 5),
            'courier_rating' => null,
            'comment' => fake()->optional()->sentence(),
            'tags' => null,
            'store_reply' => null,
            'store_reply_date' => null,
            'published' => true,
        ];
    }

    public function forStore(Store $store): static
    {
        return $this->state(fn (array $attributes): array => [
            'store_id' => $store->id,
        ]);
    }

    public function rating(int $rating): static
    {
        return $this->state(fn (array $attributes): array => [
            'rating' => $rating,
        ]);
    }

    public function replied(): static
    {
        return $this->state(fn (array $attributes): array => [
            'store_reply' => fake()->sentence(),
            'store_reply_date' => now(),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published' => false,
        ]);
    }
}
