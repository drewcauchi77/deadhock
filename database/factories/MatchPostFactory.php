<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Matches;
use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchPost>
 */
final class MatchPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => Matches::factory(),
            'player_id' => Player::factory(),
            'subscription_id' => Subscription::factory(),
            'discord_message_id' => fake()->numerify('##################'),
            'posted_at' => now(),
        ];
    }
}
