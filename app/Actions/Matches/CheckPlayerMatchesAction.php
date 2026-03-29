<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Models\Matches;
use App\Models\Player;
use App\Services\Deadlock\PlayerApiService;

final readonly class CheckPlayerMatchesAction
{
    public function __construct(
        private PlayerApiService $playerApiService,
        private FetchAndCacheMatchAction $fetchAndCacheMatchAction,
        private SendToSubscriptionsAction $sendToSubscriptionsAction,
    ) {}

    public function handle(Player $player): void
    {
        $matches = $this->playerApiService->getMatchHistory($player->steam_id);

        if ($matches === null || $matches === []) {
            return;
        }

        if ($player->last_checked_at === null) {
            $matches = [array_shift($matches)];
        }

        /** @var list<Matches> $newMatches */
        $newMatches = [];
        $highestFetchedMatchId = null;
        /** @var list<int> $pendingMatchIds */
        $pendingMatchIds = [];

        foreach ($matches as $match) {
            /** @var array{match_id: int} $match */
            $matchId = (string) $match['match_id'];

            $existing = Matches::query()->where('match_id', $matchId)->first();

            if ($existing instanceof Matches) {
                if ($existing->retries_left === null) {
                    break;
                }

                if ($existing->retries_left === 0) {
                    continue;
                }
            }

            $storedMatch = $this->fetchAndCacheMatchAction->handle($matchId);

            if ($storedMatch instanceof Matches && $storedMatch->retries_left === null) {
                $newMatches[] = $storedMatch;
                $highestFetchedMatchId = max($highestFetchedMatchId, (int) $matchId);
            } else {
                $pendingMatchIds[] = (int) $matchId;
            }
        }

        if ($highestFetchedMatchId !== null && $pendingMatchIds !== []) {
            $olderPending = array_filter($pendingMatchIds, fn (int $id): bool => $id < $highestFetchedMatchId);

            if ($olderPending !== []) {
                Matches::query()
                    ->whereIn('match_id', array_map(strval(...), $olderPending))
                    ->where('retries_left', '>', 0)
                    ->update(['retries_left' => 0]);
            }
        }

        foreach (array_reverse($newMatches) as $storedMatch) {
            $this->sendToSubscriptionsAction->handle($storedMatch);
        }

        $player->update(['last_checked_at' => now()]);
    }
}
