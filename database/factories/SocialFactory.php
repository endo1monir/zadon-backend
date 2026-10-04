<?php

namespace Database\Factories;

use App\Models\Social;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Social>
 */
class SocialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name_ar' => $name,
            'name_en' => Str::title($name),
            'icon' => null,
            'link' => 'https://example.com/'.$name,
        ];
    }

    public function withIcon(string $path): static
    {
        return $this->state(fn (array $attributes): array => [
            'icon' => $path,
        ]);
    }
}
