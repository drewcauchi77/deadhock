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

        foreach ($matches as $match) {
            /** @var array{match_id: int} $match */
            $matchId = (string) $match['match_id'];

            if (Matches::query()->where('match_id', $matchId)->exists()) {
                break;
            }

            $storedMatch = $this->fetchAndCacheMatchAction->handle($matchId);

            if (! $storedMatch instanceof Matches) {
                Matches::query()->create(['match_id' => $matchId]);

                continue;
            }

            $newMatches[] = $storedMatch;
        }

        foreach (array_reverse($newMatches) as $storedMatch) {
            $this->sendToSubscriptionsAction->handle($storedMatch);
        }

        $player->update(['last_checked_at' => now()]);
    }
}
