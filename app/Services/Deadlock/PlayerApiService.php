<?php

declare(strict_types=1);

namespace App\Services\Deadlock;

final readonly class PlayerApiService
{
    public function __construct(private DeadlockApiService $deadlockApiService) {}

    /**
     * @note Stub can be found at stubs/MatchHistory
     *
     * @return array<mixed>|null
     */
    public function getMatchHistory(string $accountId): ?array
    {
        return $this->deadlockApiService->get(sprintf('players/%s/match-history', $accountId));
    }
}
