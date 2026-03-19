<?php

declare(strict_types=1);

use App\Actions\Matches\FetchAndCacheMatchAction;
use App\Models\Matches;
use App\Models\Player;
use App\Services\Deadlock\MatchApiService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\mock;

it('returns existing match without calling API', function (): void {
    $match = Matches::factory()->create(['match_id' => '99999']);

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldNotReceive('getMatchMetadata');

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('99999');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($match->id);
});

it('returns null when API returns null', function (): void {
    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->andReturnNull();

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('12345');

    expect($result)->toBeNull();
});

it('creates match and caches data from API', function (): void {
    $matchData = [
        'match_info' => [
            'start_time' => 1710000000,
            'players' => [
                ['account_id' => 111],
                ['account_id' => 222],
            ],
        ],
    ];

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->with('55555')->andReturn($matchData);

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('55555');

    expect($result)->not->toBeNull()
        ->and($result->match_id)->toBe('55555')
        ->and($result->match_started_at)->not->toBeNull()
        ->and(Cache::has('match.55555'))->toBeTrue();
});

it('links tracked players to the match', function (): void {
    $player = Player::factory()->create(['steam_id' => '111']);

    $matchData = [
        'match_info' => [
            'start_time' => 1710000000,
            'players' => [
                ['account_id' => 111],
                ['account_id' => 999],
            ],
        ],
    ];

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->andReturn($matchData);

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('77777');

    expect($result->matchPlayers)->toHaveCount(1)
        ->and($result->matchPlayers->first()->player_id)->toBe($player->id);
});

it('does not create match players for untracked accounts', function (): void {
    $matchData = [
        'match_info' => [
            'start_time' => 1710000000,
            'players' => [
                ['account_id' => 888],
            ],
        ],
    ];

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->andReturn($matchData);

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('66666');

    expect($result->matchPlayers)->toHaveCount(0);
});
