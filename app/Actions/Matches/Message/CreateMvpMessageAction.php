<?php

declare(strict_types=1);

namespace App\Actions\Matches\Message;

use App\Models\Matches;
use App\Models\Subscription;

final readonly class CreateMvpMessageAction
{
    use CreateMessageAction;

    /**
     * @param  array<int, array{id: int, name: string}>  $heroes
     */
    public function handle(Matches $match, Subscription $subscription, array $heroes = []): string
    {
        $heroes = $this->resolveHeroes($heroes);
        $matchData = $this->resolveMatchData($match);

        if ($matchData === null) {
            return '';
        }

        $niceNames = $this->resolveNiceNames($subscription);

        /** @var array{match_info: array{players: array<int, array{account_id: int, hero_id: int, mvp_rank?: int|null}>}} $matchData */
        $players = $matchData['match_info']['players'];

        $mvpDescriptions = [];
        $keyPlayerDescriptions = [];

        foreach ($players as $player) {
            $accountId = $player['account_id'];
            if (! isset($niceNames[$accountId])) {
                continue;
            }

            if (! isset($player['mvp_rank'])) {
                continue;
            }

            if (! in_array($player['mvp_rank'], [1, 2, 3], true)) {
                continue;
            }

            $heroName = $heroes[$player['hero_id']]['name'] ?? 'Unknown';
            $description = sprintf('%s (playing as %s)', $niceNames[$accountId], $heroName);

            if ($player['mvp_rank'] === 1) {
                $mvpDescriptions[] = $description;
            } else {
                $keyPlayerDescriptions[] = $description;
            }
        }

        $lines = [];

        if ($mvpDescriptions !== []) {
            $lines[] = sprintf('%s was the MVP.', $this->joinNames($mvpDescriptions));
        }

        if ($keyPlayerDescriptions !== []) {
            $verb = count($keyPlayerDescriptions) === 1 ? 'was a key player' : 'were key players';
            $lines[] = sprintf('%s %s.', $this->joinNames($keyPlayerDescriptions), $verb);
        }

        return implode("\n", $lines);
    }
}
