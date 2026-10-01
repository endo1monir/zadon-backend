<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nameEn = fake()->unique()->words(2, true);

        return [
            'type' => fake()->randomElement(['store', 'product']),
            'name_ar' => fake()->unique()->words(2, true),
            'name_en' => Str::title($nameEn),
            'slug' => Str::slug($nameEn).'-'.fake()->unique()->numberBetween(1, 999999),
            'icon' => null,
            'parent_id' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function forStores(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'store',
        ]);
    }

    public function forProducts(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'product',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
