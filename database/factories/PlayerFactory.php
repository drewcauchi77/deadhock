<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
final class PlayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'steam_id' => fake()->numerify('765611980########'),
            'last_checked_at' => now(),
        ];
    }

    public function unchecked(): self
    {
        return $this->state([
            'last_checked_at' => null,
        ]);
    }
}
