<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClockIn>
 */
class ClockInFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'registered_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => fake()->dateTimeBetween('today 08:00', 'today 18:00'),
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => fake()->dateTimeBetween($date . ' 08:00', $date . ' 18:00'),
        ]);
    }

    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => fake()->dateTimeBetween('08:00', '12:00'),
        ]);
    }

    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'registered_at' => fake()->dateTimeBetween('13:00', '18:00'),
        ]);
    }
} 