<?php

declare(strict_types=1);

namespace App\Services\Deadlock;

final readonly class MatchApiService
{
    public function __construct(private DeadlockApiService $deadlockApiService) {}

    public function getMatchMetadata(string $matchId, int $value): void
    {
        $this->deadlockApiService->get(sprintf('matches/%d/metadata', $matchId), $value);
    }
}
