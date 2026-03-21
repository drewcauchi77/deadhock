<?php

declare(strict_types=1);

use App\Actions\Matches\Message\CreateMvpMessageAction;
use App\Models\Matches;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;

it('returns mvp message for tracked player with rank 1', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '12345']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 12345, 'hero_id' => 1, 'mvp_rank' => 1],
                ['account_id' => 99999, 'hero_id' => 2, 'mvp_rank' => 2],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
    ];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) was the MVP.');
});

it('returns key player message for tracked players with rank 2 or 3', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '12345']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 12345, 'hero_id' => 1, 'mvp_rank' => 2],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) was a key player.');
});

it('returns both mvp and key player lines when multiple tracked players have ranks', function (): void {
    $subscription = Subscription::factory()->create();
    $player1 = Player::factory()->create(['steam_id' => '11111']);
    $player2 = Player::factory()->create(['steam_id' => '22222']);
    $player1->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);
    $player2->subscriptions()->attach($subscription, ['nice_name' => 'Bromar']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 11111, 'hero_id' => 1, 'mvp_rank' => 1],
                ['account_id' => 22222, 'hero_id' => 2, 'mvp_rank' => 3],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
    ];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe("Ace (playing as Infernus) was the MVP.\nBromar (playing as Seven) was a key player.");
});

it('returns empty string when no tracked players have mvp ranks', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '12345']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 99999, 'hero_id' => 1, 'mvp_rank' => 1],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('');
});

it('returns empty string when cache is missing', function (): void {
    $subscription = Subscription::factory()->create();
    $match = Matches::factory()->create(['match_id' => '99999']);

    $result = (new CreateMvpMessageAction)->handle($match, $subscription);

    expect($result)->toBe('');
});

it('uses were key players for multiple key players', function (): void {
    $subscription = Subscription::factory()->create();
    $player1 = Player::factory()->create(['steam_id' => '11111']);
    $player2 = Player::factory()->create(['steam_id' => '22222']);
    $player1->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);
    $player2->subscriptions()->attach($subscription, ['nice_name' => 'Bromar']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 11111, 'hero_id' => 1, 'mvp_rank' => 2],
                ['account_id' => 22222, 'hero_id' => 2, 'mvp_rank' => 3],
            ],
        ],
    ]);

    $heroes = [
        1 => ['id' => 1, 'name' => 'Infernus'],
        2 => ['id' => 2, 'name' => 'Seven'],
    ];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('Ace (playing as Infernus) and Bromar (playing as Seven) were key players.');
});

it('skips tracked player without mvp_rank set', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '12345']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 12345, 'hero_id' => 1],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('');
});

it('skips tracked player with mvp_rank outside 1 2 3', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create(['steam_id' => '12345']);
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create(['match_id' => '99999']);

    Cache::put('match.99999', [
        'match_info' => [
            'players' => [
                ['account_id' => 12345, 'hero_id' => 1, 'mvp_rank' => 5],
            ],
        ],
    ]);

    $heroes = [1 => ['id' => 1, 'name' => 'Infernus']];

    $result = (new CreateMvpMessageAction)->handle($match, $subscription, $heroes);

    expect($result)->toBe('');
});
