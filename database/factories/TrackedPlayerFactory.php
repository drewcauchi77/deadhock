<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrackedPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackedPlayer>
 */
final class TrackedPlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'steam_id' => fake()->numerify('765611980########'),
            'last_checked_at' => now(),
        ];
    }

    public function unchecked(): static
    {
        return $this->state([
            'last_checked_at' => null,
        ]);
    }
}
