<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Matches;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Matches>
 */
final class MatchesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => fake()->numerify('###########'),
            'match_started_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'fetched_at' => now(),
            'retries_left' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'retries_left' => 3,
            'match_started_at' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => [
            'retries_left' => 0,
            'match_started_at' => null,
        ]);
    }
}
