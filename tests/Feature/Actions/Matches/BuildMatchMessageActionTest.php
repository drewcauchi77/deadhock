<?php

declare(strict_types=1);

use App\Actions\Matches\BuildMatchMessageAction;
use App\Models\Matches;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;

it('returns fallback message when cache is empty', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, [1 => ['id' => 1, 'name' => 'Infernus']]);

    expect($result)->toBe('Match #12345 results');
});

it('returns fallback message when no tracked players in match', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 999, 'hero_id' => 1, 'team' => 0],
            ],
        ],
    ]);

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, [1 => ['id' => 1, 'name' => 'Infernus']]);

    expect($result)->toBe('Match #12345 results');
});

it('builds message for single tracked player who won', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 0],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];
    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) won a game.');
});

it('builds message for single tracked player who lost', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 1],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];
    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) lost a game.');
});

it('builds combined message for two tracked players with same outcome', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();

    $player1 = Player::factory()->create(['steam_id' => '111']);
    $player1->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $player2 = Player::factory()->create(['steam_id' => '222']);
    $player2->subscriptions()->attach($subscription, ['nice_name' => 'Bromar']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 0],
                ['account_id' => 222, 'hero_id' => 2, 'team' => 0],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
    ];

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) and Bromar (playing as Seven) won a game.');
});

it('builds separate lines for tracked players with different outcomes', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();

    $player1 = Player::factory()->create(['steam_id' => '111']);
    $player1->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $player2 = Player::factory()->create(['steam_id' => '222']);
    $player2->subscriptions()->attach($subscription, ['nice_name' => 'Bromar']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 0],
                ['account_id' => 222, 'hero_id' => 2, 'team' => 1],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
    ];

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, $heroes);

    expect($result)->toBe("Ace (playing as Infernus) won a game.\nBromar (playing as Seven) lost a game.");
});

it('builds message with three tracked players using comma and', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();

    $player1 = Player::factory()->create(['steam_id' => '111']);
    $player1->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $player2 = Player::factory()->create(['steam_id' => '222']);
    $player2->subscriptions()->attach($subscription, ['nice_name' => 'Bromar']);

    $player3 = Player::factory()->create(['steam_id' => '333']);
    $player3->subscriptions()->attach($subscription, ['nice_name' => 'Charlie']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 1,
            'players' => [
                ['account_id' => 111, 'hero_id' => 1, 'team' => 1],
                ['account_id' => 222, 'hero_id' => 2, 'team' => 1],
                ['account_id' => 333, 'hero_id' => 3, 'team' => 1],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
        3 => ['id' => 3, 'name' => 'Vindicta'],
    ];

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus), Bromar (playing as Seven) and Charlie (playing as Vindicta) won a game.');
});

it('uses Unknown for missing hero data', function (): void {
    $match = Matches::factory()->create(['match_id' => '12345']);
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '111']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Cache::put('match.12345', [
        'match_info' => [
            'winning_team' => 0,
            'players' => [
                ['account_id' => 111, 'hero_id' => 9999, 'team' => 0],
            ],
        ],
    ]);

    $result = resolve(BuildMatchMessageAction::class)->handle($match, $subscription, []);

    expect($result)->toContain('playing as Unknown');
});
