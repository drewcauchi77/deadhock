<?php

declare(strict_types=1);

namespace App\Actions\Matches\Message;

use App\Models\Matches;
use App\Models\Subscription;

final readonly class CreateMatchMessageAction
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
            return sprintf('Match #%s results', $match->match_id);
        }

        /** @var array{match_info: array{winning_team: int, players: array<int, array{account_id: int, hero_id: int, team: int}>}} $matchData */
        $winningTeam = $matchData['match_info']['winning_team'];
        $niceNames = $this->resolveNiceNames($subscription);

        $players = $matchData['match_info']['players'];

        $trackedDescriptions = [];
        foreach ($players as $player) {
            $accountId = $player['account_id'];

            if (! isset($niceNames[$accountId])) {
                continue;
            }

            $heroName = $heroes[$player['hero_id']]['name'] ?? 'Unknown';
            $outcome = $player['team'] === $winningTeam ? 'won' : 'lost';

            $trackedDescriptions[] = [
                'text' => sprintf('%s (playing as %s)', $niceNames[$accountId], $heroName),
                'outcome' => $outcome,
            ];
        }

        if ($trackedDescriptions === []) {
            return sprintf('Match #%s results', $match->match_id);
        }

        $allSameOutcome = count(array_unique(array_column($trackedDescriptions, 'outcome'))) === 1;

        if ($allSameOutcome) {
            $names = array_column($trackedDescriptions, 'text');
            $outcome = $trackedDescriptions[0]['outcome'];

            return sprintf('%s %s a game.', $this->joinNames($names), $outcome);
        }

        $lines = [];
        foreach ($trackedDescriptions as $description) {
            $lines[] = sprintf('%s %s a game.', $description['text'], $description['outcome']);
        }

        return implode("\n", $lines);
    }
}
