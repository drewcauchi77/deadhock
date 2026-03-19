<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Models\Matches;
use App\Models\Player;
use App\Services\Deadlock\MatchApiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

final readonly class FetchAndCacheMatchAction
{
    public function __construct(private MatchApiService $matchApiService) {}

    public function handle(string $matchId): ?Matches
    {
        $existing = Matches::query()->where('match_id', $matchId)->first();

        if ($existing instanceof Matches) {
            return $existing;
        }

        $data = $this->matchApiService->getMatchMetadata($matchId);

        if ($data === null) {
            return null;
        }

        Cache::put('match.'.$matchId, $data, now()->addHours(24));

        /** @var array{match_info: array{start_time?: int, players: array<int, array{account_id: int}>}} $data */
        $match = Matches::query()->create([
            'match_id' => $matchId,
            'match_started_at' => isset($data['match_info']['start_time'])
                ? Date::createFromTimestamp($data['match_info']['start_time'])
                : null,
        ]);

        foreach ($data['match_info']['players'] as $player) {
            $trackedPlayer = Player::query()->where('steam_id', (string) $player['account_id'])->first();

            if ($trackedPlayer instanceof Player) {
                $match->matchPlayers()->create([
                    'player_id' => $trackedPlayer->id,
                ]);
            }
        }

        return $match;
    }
}
