<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Models\Matches;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

final readonly class BuildMatchMessageAction
{
    /**
     * @param  array<int, array{id: int, name: string}>  $heroes
     */
    public function handle(Matches $match, Subscription $subscription, array $heroes = []): string
    {
        if ($heroes === []) {
            /** @var array<int, array{id: int, name: string}> $heroData */
            $heroData = json_decode(File::get(database_path('data/heroes.json')), true);

            /** @var array<int, array{id: int, name: string}> $heroes */
            $heroes = collect($heroData)->keyBy('id')->all();
        }

        /** @var array{match_info: array{winning_team: int, players: array<int, array{account_id: int, hero_id: int, team: int}>}}|null $matchData */
        $matchData = Cache::get('match.'.$match->match_id);

        if ($matchData === null) {
            return sprintf('Match #%s results', $match->match_id);
        }

        $winningTeam = $matchData['match_info']['winning_team'];

        $niceNames = [];
        foreach ($subscription->players()->withPivot('nice_name')->get() as $player) {
            /** @var Player&object{pivot: object{nice_name: string}} $player */
            $niceNames[(int) $player->steam_id] = $player->pivot->nice_name;
        }

        /** @var array<int, array{account_id: int, hero_id: int, team: int}> $players */
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

    /**
     * @param  array<int, string>  $names
     */
    private function joinNames(array $names): string
    {
        if (count($names) === 1) {
            return $names[0];
        }

        if (count($names) === 2) {
            return $names[0].' and '.$names[1];
        }

        $last = array_pop($names);

        return implode(', ', $names).' and '.$last;
    }
}
