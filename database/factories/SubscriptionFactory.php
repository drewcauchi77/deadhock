<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guild_id' => fake()->numerify('01425362########'),
            'channel_id' => fake()->numerify('11425362########'),
        ];
    }
}
