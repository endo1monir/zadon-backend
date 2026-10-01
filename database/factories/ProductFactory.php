<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'store_id' => Store::factory(),
            'category_id' => null,
            'name_ar' => $name,
            'name_en' => Str::title($name),
            'sku' => strtoupper(Str::random(8)),
            'barcode' => fake()->numerify('62#############'),
            'price' => fake()->randomFloat(2, 10, 500),
            'original_price' => null,
            'cost_price' => fake()->randomFloat(2, 5, 300),
            'stock' => fake()->numberBetween(0, 200),
            'min_stock_alert' => fake()->numberBetween(0, 20),
            'unit_ar' => fake()->randomElement(['قطعة', 'كيلو', 'علبة']),
            'unit_en' => fake()->randomElement(['piece', 'kg', 'box']),
            'image' => null,
            'country_of_origin' => fake()->randomElement(['Egypt', 'Saudi Arabia', 'Turkey', 'USA']),
            'storage_method' => null,
            'is_prescription_required' => false,
            'storage_temp' => 'ambient',
            'expiry_date' => fake()->optional()->date(),
            'is_active' => true,
            'sales_count' => fake()->numberBetween(0, 1000),
            'description_ar' => fake()->sentence(),
            'description_en' => fake()->sentence(),
            'options' => null,
        ];
    }

    public function withCategory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'category_id' => Category::factory()->create(['type' => 'product'])->id,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes): array => [
            'stock' => 1,
            'min_stock_alert' => 5,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
