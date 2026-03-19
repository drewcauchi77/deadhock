<?php

declare(strict_types=1);

use App\Services\Deadlock\DeadlockApiService;
use App\Services\Deadlock\MatchApiService;

use function Pest\Laravel\mock;

it('calls the correct endpoint for match metadata', function (): void {
    $mockApi = mock(DeadlockApiService::class);
    $mockApi->shouldReceive('get')
        ->once()
        ->with('matches/99999/metadata')
        ->andReturn(['match_info' => ['duration_s' => 1800]]);

    $service = resolve(MatchApiService::class);
    $result = $service->getMatchMetadata('99999');

    expect($result)->toBe(['match_info' => ['duration_s' => 1800]]);
});

it('returns null when api returns null', function (): void {
    $mockApi = mock(DeadlockApiService::class);
    $mockApi->shouldReceive('get')->once()->andReturnNull();

    $service = resolve(MatchApiService::class);
    $result = $service->getMatchMetadata('99999');

    expect($result)->toBeNull();
});
