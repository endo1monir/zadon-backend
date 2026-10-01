<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
        ];
    }

    public function fromUser(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory(),
        ]);
    }
}
