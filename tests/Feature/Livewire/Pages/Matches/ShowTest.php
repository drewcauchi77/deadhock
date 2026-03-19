<?php

declare(strict_types=1);

use App\Livewire\Pages\Matches\Show;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

function matchData(array $players = []): array
{
    return [
        'match_info' => [
            'duration_s' => 1800,
            'winning_team' => 0,
            'players' => $players !== [] ? $players : [
                ['account_id' => 111, 'player_slot' => 0, 'hero_id' => 1, 'team' => 0, 'net_worth' => 45000, 'kills' => 5, 'deaths' => 2, 'assists' => 10, 'last_hits' => 150, 'denies' => 12, 'stats' => [['player_damage' => 25000, 'player_healing' => 3000]]],
                ['account_id' => 222, 'player_slot' => 1, 'hero_id' => 2, 'team' => 0, 'net_worth' => 38000, 'kills' => 3, 'deaths' => 4, 'assists' => 8, 'last_hits' => 120, 'denies' => 5, 'stats' => [['player_damage' => 18000, 'player_healing' => 1000]]],
                ['account_id' => 333, 'player_slot' => 2, 'hero_id' => 3, 'team' => 0, 'net_worth' => 52000, 'kills' => 7, 'deaths' => 1, 'assists' => 6, 'last_hits' => 200, 'denies' => 20, 'stats' => []],
                ['account_id' => 444, 'player_slot' => 3, 'hero_id' => 4, 'team' => 0, 'net_worth' => 30000, 'kills' => 2, 'deaths' => 5, 'assists' => 12, 'last_hits' => 80, 'denies' => 3, 'stats' => []],
                ['account_id' => 555, 'player_slot' => 4, 'hero_id' => 5, 'team' => 0, 'net_worth' => 41000, 'kills' => 4, 'deaths' => 3, 'assists' => 9, 'last_hits' => 100, 'denies' => 7, 'stats' => []],
                ['account_id' => 666, 'player_slot' => 5, 'hero_id' => 6, 'team' => 0, 'net_worth' => 25000, 'kills' => 1, 'deaths' => 6, 'assists' => 4, 'last_hits' => 60, 'denies' => 1, 'stats' => []],
                ['account_id' => 777, 'player_slot' => 6, 'hero_id' => 7, 'team' => 1, 'net_worth' => 42000, 'kills' => 6, 'deaths' => 3, 'assists' => 5, 'last_hits' => 170, 'denies' => 15, 'stats' => []],
                ['account_id' => 888, 'player_slot' => 7, 'hero_id' => 8, 'team' => 1, 'net_worth' => 48000, 'kills' => 8, 'deaths' => 2, 'assists' => 7, 'last_hits' => 190, 'denies' => 18, 'stats' => []],
                ['account_id' => 999, 'player_slot' => 8, 'hero_id' => 9, 'team' => 1, 'net_worth' => 20000, 'kills' => 0, 'deaths' => 7, 'assists' => 3, 'last_hits' => 50, 'denies' => 0, 'stats' => []],
                ['account_id' => 1010, 'player_slot' => 9, 'hero_id' => 10, 'team' => 1, 'net_worth' => 35000, 'kills' => 3, 'deaths' => 4, 'assists' => 6, 'last_hits' => 110, 'denies' => 8, 'stats' => []],
                ['account_id' => 1111, 'player_slot' => 10, 'hero_id' => 11, 'team' => 1, 'net_worth' => 40000, 'kills' => 5, 'deaths' => 3, 'assists' => 8, 'last_hits' => 130, 'denies' => 10, 'stats' => []],
                ['account_id' => 1212, 'player_slot' => 11, 'hero_id' => 12, 'team' => 1, 'net_worth' => 22000, 'kills' => 2, 'deaths' => 5, 'assists' => 4, 'last_hits' => 70, 'denies' => 2, 'stats' => []],
            ],
        ],
    ];
}

it('renders successfully with cached match data', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', matchData());

    Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription])
        ->assertSuccessful()
        ->assertSee('Match #12345');
});

it('aborts with 404 when match is not cached', function (): void {
    $subscription = Subscription::factory()->create();

    Livewire::test(Show::class, ['matchId' => '99999', 'subscription' => $subscription])
        ->assertNotFound();
});

it('splits players into archmother and hidden king teams', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', matchData());

    $component = Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription]);

    expect($component->get('teamArchmother'))->toHaveCount(6)
        ->and($component->get('teamHiddenKing'))->toHaveCount(6);
});

it('displays tracked player nice names', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', matchData());

    Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription])
        ->assertSee('Ace');
});

it('assigns Player N to untracked players', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', matchData());

    $component = Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $hiddenKing = $component->get('teamHiddenKing');

    expect($hiddenKing[0]['display_name'])->toBe('Player 1')
        ->and($hiddenKing[1]['display_name'])->toBe('Player 2');
});

it('marks tracked players with is_tracked flag', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', matchData());

    $component = Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $hiddenKing = $component->get('teamHiddenKing');

    expect($hiddenKing[0]['is_tracked'])->toBeTrue()
        ->and($hiddenKing[1]['is_tracked'])->toBeFalse();
});

it('extracts player damage and healing from last stat entry', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', matchData());

    $component = Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $firstPlayer = $component->get('teamHiddenKing')[0];

    expect($firstPlayer['player_damage'])->toBe(25000)
        ->and($firstPlayer['player_healing'])->toBe(3000);
});

it('defaults damage and healing to zero when stats are empty', function (): void {
    $subscription = Subscription::factory()->create();
    Cache::put('match.12345', matchData());

    $component = Livewire::test(Show::class, ['matchId' => '12345', 'subscription' => $subscription]);

    $thirdPlayer = $component->get('teamHiddenKing')[2];

    expect($thirdPlayer['player_damage'])->toBe(0)
        ->and($thirdPlayer['player_healing'])->toBe(0);
});
