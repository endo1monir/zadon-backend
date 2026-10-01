<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\City;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => null,
            'category_id' => null,
            'city_id' => City::factory(),
            'name_ar' => fake()->unique()->company(),
            'name_en' => fake()->unique()->company(),
            'logo' => null,
            'cover_image' => null,
            'rating' => fake()->randomFloat(2, 1, 5),
            'rating_count' => fake()->numberBetween(0, 500),
            'address' => fake()->address(),
            'phone' => fake()->numerify('01#########'),
            'email' => fake()->unique()->safeEmail(),
            'cr_number' => fake()->numerify('#########'),
            'vat_number' => fake()->numerify('#########'),
            'status' => fake()->randomElement(['open', 'busy', 'closed']),
            'prep_time_min' => fake()->numberBetween(5, 90),
            'delivery_fee' => fake()->randomFloat(2, 0, 50),
            'min_order' => fake()->randomFloat(2, 0, 200),
            'manager_name' => fake()->name(),
            'is_verified' => fake()->boolean(),
            'is_open_24_7' => false,
            'opening_time' => '09:00',
            'closing_time' => '23:00',
            'delivery_radius_km' => fake()->randomFloat(2, 2, 30),
            'is_active' => true,
        ];
    }

    public function withOwner(): static
    {
        return $this->state(fn (array $attributes): array => [
            'owner_id' => User::factory(),
        ]);
    }

    public function withCategory(?string $type = 'store'): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => Category::factory()->create(['type' => $type])->id,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_verified' => false,
        ]);
    }
}
