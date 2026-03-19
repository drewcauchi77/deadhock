<?php

declare(strict_types=1);

use App\Services\Deadlock\DeadlockApiService;
use App\Services\Deadlock\PlayerApiService;

use function Pest\Laravel\mock;

it('calls the correct endpoint for match history', function (): void {
    $mockApi = mock(DeadlockApiService::class);
    $mockApi->shouldReceive('get')
        ->once()
        ->with('players/12345/match-history')
        ->andReturn([['match_id' => 100]]);

    $service = resolve(PlayerApiService::class);
    $result = $service->getMatchHistory('12345');

    expect($result)->toBe([['match_id' => 100]]);
});

it('returns null when api returns null', function (): void {
    $mockApi = mock(DeadlockApiService::class);
    $mockApi->shouldReceive('get')->once()->andReturnNull();

    $service = resolve(PlayerApiService::class);
    $result = $service->getMatchHistory('12345');

    expect($result)->toBeNull();
});
