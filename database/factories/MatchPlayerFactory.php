<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MatchPlayer>
 */
final class MatchPlayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'match_id' => Matches::factory(),
            'player_id' => Player::factory(),
        ];
    }
}
