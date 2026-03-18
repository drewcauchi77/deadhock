<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $subscriptions = Subscription::factory(5)->create();

        $players = Player::factory(16)->create();
        Player::factory(4)->unchecked()->create();

        $subscriptions->each(function (Subscription $subscription) use ($players): void {
            $players->random(fake()->numberBetween(3, 5))
                ->each(function (Player $player) use ($subscription): void {
                    $subscription->players()->attach($player->id, ['nice_name' => fake()->userName()]);
                });
        });

        $matches = Matches::factory(20)->create();

        $matches->each(function (Matches $match) use ($players, $subscriptions): void {
            $matchPlayers = $players->random(fake()->numberBetween(2, 4));

            $matchPlayers->each(function (Player $player) use ($match): void {
                MatchPlayer::factory()->create([
                    'match_id' => $match->id,
                    'player_id' => $player->id,
                ]);
            });

            $matchPlayerIds = $matchPlayers->pluck('id');

            $subscriptions
                ->filter(fn (Subscription $subscription): bool => $subscription->players()
                    ->whereIn('players.id', $matchPlayerIds)
                    ->exists()
                )
                ->each(function (Subscription $subscription) use ($match, $matchPlayers): void {
                    MatchPost::factory()->create([
                        'match_id' => $match->id,
                        'player_id' => $matchPlayers->firstOrFail()->id,
                        'subscription_id' => $subscription->id,
                    ]);
                });
        });
    }
}
