<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DiscordSubscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscordSubscription>
 */
final class DiscordSubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guild_id' => fake()->md5(),
            'channel_id' => fake()->md5(),
        ];
    }
}
