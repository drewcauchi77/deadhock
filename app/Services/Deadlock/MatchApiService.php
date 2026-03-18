<?php

declare(strict_types=1);

namespace App\Services\Deadlock;

final readonly class MatchApiService
{
    public function __construct(private DeadlockApiService $deadlockApiService) {}

    /**
     * @note Stub can be found at stubs/MatchMetadata
     *
     * @return array<mixed>|null
     */
    public function getMatchMetadata(string $matchId): ?array
    {
        return $this->deadlockApiService->get(sprintf('matches/%s/metadata', $matchId));
    }
}
