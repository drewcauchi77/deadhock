<?php

declare(strict_types=1);

use App\Livewire\Pages\Matches\Mvp;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function mvpMatchData(array $players = []): array
{
    return [
        'match_info' => [
            'winning_team' => 0,
            'players' => $players !== [] ? $players : [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 0, 'mvp_rank' => 1, 'kills' => 12, 'deaths' => 1, 'assists' => 8, 'net_worth' => 55000],
                ['account_id' => 222, 'hero_id' => 2, 'team' => 0, 'mvp_rank' => 2, 'kills' => 8, 'deaths' => 3, 'assists' => 10, 'net_worth' => 42000],
                ['account_id' => 333, 'hero_id' => 3, 'team' => 0, 'mvp_rank' => 3, 'kills' => 6, 'deaths' => 4, 'assists' => 14, 'net_worth' => 38000],
                ['account_id' => 444, 'hero_id' => 4, 'team' => 0, 'kills' => 3, 'deaths' => 5, 'assists' => 6, 'net_worth' => 28000],
            ],
        ],
    ];
}

it('renders successfully with cached match data', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription])
        ->assertSuccessful();
});

it('aborts with 404 when match is not cached', function (): void {
    $subscription = Subscription::factory()->create();

    Livewire::test(Mvp::class, ['matchId' => '99999', 'subscription' => $subscription])
        ->assertNotFound();
});

it('only includes players with mvp_rank 1, 2, or 3', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    expect($component->get('mvpPlayers'))->toHaveCount(3);
});

it('sorts players by mvp_rank ascending', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $ranks = array_column($component->get('mvpPlayers'), 'mvp_rank');

    expect($ranks)->toBe([1, 2, 3]);
});

it('displays tracked player nice names', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $mvpPlayer = $component->get('mvpPlayers')[0];

    expect($mvpPlayer['display_name'])->toBe('Ace')
        ->and($mvpPlayer['is_tracked'])->toBeTrue();
});

it('uses hero name for untracked players', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $mvpPlayer = $component->get('mvpPlayers')[0];

    expect($mvpPlayer['is_tracked'])->toBeFalse()
        ->and($mvpPlayer['display_name'])->toBe($mvpPlayer['hero_name']);
});

it('sets winning team from match data', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    expect($component->get('winningTeam'))->toBe(0);
});

it('includes player stats in mvp player data', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', mvpMatchData());

    $component = Livewire::test(Mvp::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $mvpPlayer = $component->get('mvpPlayers')[0];

    expect($mvpPlayer['kills'])->toBe(12)
        ->and($mvpPlayer['deaths'])->toBe(1)
        ->and($mvpPlayer['assists'])->toBe(8)
        ->and($mvpPlayer['net_worth'])->toBe(55000);
});
