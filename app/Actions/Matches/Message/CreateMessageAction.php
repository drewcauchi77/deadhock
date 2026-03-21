<?php

declare(strict_types=1);

namespace App\Actions\Matches\Message;

use App\Models\Matches;
use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

trait CreateMessageAction
{
    /**
     * @param  array<int, array{id: int, name: string}>  $heroes
     * @return array<int, array{id: int, name: string}>
     */
    protected function resolveHeroes(array $heroes): array
    {
        if ($heroes !== []) {
            return $heroes;
        }

        /** @var array<int, array{id: int, name: string}> $heroData */
        $heroData = json_decode(File::get(database_path('data/heroes.json')), true);

        /** @var array<int, array{id: int, name: string}> $keyed */
        $keyed = collect($heroData)->keyBy('id')->all();

        return $keyed;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function resolveMatchData(Matches $match): ?array
    {
        /** @var array<string, mixed>|null $matchData */
        $matchData = Cache::get('match.'.$match->match_id);

        return $matchData;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveNiceNames(Subscription $subscription): array
    {
        $niceNames = [];
        foreach ($subscription->players()->withPivot('nice_name')->get() as $player) {
            /** @var Player&object{pivot: object{nice_name: string}} $player */
            $niceNames[(int) $player->steam_id] = $player->pivot->nice_name;
        }

        return $niceNames;
    }

    /**
     * @param  array<int, string>  $names
     */
    protected function joinNames(array $names): string
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
