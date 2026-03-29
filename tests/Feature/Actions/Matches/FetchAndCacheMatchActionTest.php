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

it('creates a pending match record when API returns null', function (): void {
    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->andReturnNull();

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('12345');

    expect($result)->toBeNull();

    $match = Matches::query()->where('match_id', '12345')->first();
    expect($match)->not->toBeNull()
        ->and($match->retries_left)->toBe(2);
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
        ->and($result->retries_left)->toBeNull()
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

it('retries a pending match and succeeds', function (): void {
    $match = Matches::factory()->pending()->create(['match_id' => '11111', 'retries_left' => 2]);

    $matchData = [
        'match_info' => [
            'start_time' => 1710000000,
            'players' => [
                ['account_id' => 111],
            ],
        ],
    ];

    $player = Player::factory()->create(['steam_id' => '111']);

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->with('11111')->andReturn($matchData);

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('11111');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($match->id)
        ->and($result->retries_left)->toBeNull()
        ->and($result->match_started_at)->not->toBeNull()
        ->and($result->matchPlayers)->toHaveCount(1)
        ->and($result->matchPlayers->first()->player_id)->toBe($player->id)
        ->and(Cache::has('match.11111'))->toBeTrue();
});

it('retries a pending match and fails with retries remaining', function (): void {
    Matches::factory()->pending()->create(['match_id' => '22222', 'retries_left' => 2]);

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->with('22222')->andReturnNull();

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('22222');

    expect($result)->toBeNull();

    $match = Matches::query()->where('match_id', '22222')->first();
    expect($match->retries_left)->toBe(1);
});

it('marks match as permanently failed on last retry', function (): void {
    Matches::factory()->pending()->create(['match_id' => '33333', 'retries_left' => 1]);

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldReceive('getMatchMetadata')->once()->with('33333')->andReturnNull();

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('33333');

    expect($result)->toBeNull();

    $match = Matches::query()->where('match_id', '33333')->first();
    expect($match->retries_left)->toBe(0);
});

it('does not retry a permanently failed match', function (): void {
    $match = Matches::factory()->failed()->create(['match_id' => '44444']);

    $mockApi = mock(MatchApiService::class);
    $mockApi->shouldNotReceive('getMatchMetadata');

    $action = resolve(FetchAndCacheMatchAction::class);
    $result = $action->handle('44444');

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($match->id)
        ->and($result->retries_left)->toBe(0);
});
