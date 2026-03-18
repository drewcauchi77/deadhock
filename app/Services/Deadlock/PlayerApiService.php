<?php

declare(strict_types=1);

namespace App\Services\Deadlock;

final readonly class PlayerApiService
{
    public function __construct(private DeadlockApiService $deadlockApiService) {}

    public function getMatchHistory(string $accountId, int $value): void
    {
        $this->deadlockApiService->get(sprintf('players/%d/match-history', $accountId), $value);
    }
}
