<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Matches;

use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Livewire\Component;

final class Show extends Component
{
    public string $matchId;

    /** @var array<string, mixed> */
    public array $matchInfo = [];

    /** @var array<int, array<string, mixed>> */
    public array $teamHiddenKing = [];

    /** @var array<int, array<string, mixed>> */
    public array $teamArchmother = [];

    /** @var array<int, array<string, mixed>> */
    public array $heroes = [];

    public function mount(string $matchId, Subscription $subscription): void
    {
        $this->matchId = $matchId;

        /** @var array{match_info: array<string, mixed>}|null $match */
        $match = Cache::get('match.'.$matchId);

        abort_if($match === null, 404);

        $this->matchInfo = $match['match_info'];

        // @note https://assets.deadlock-api.com/v2/heroes
        /** @var array<int, array<string, mixed>> $heroData */
        $heroData = json_decode(File::get(database_path('data/heroes.json')), true);

        /** @var array<int, array<string, mixed>> $heroesKeyed */
        $heroesKeyed = collect($heroData)->keyBy('id')->all();
        $this->heroes = $heroesKeyed;

        /** @var array<int, string> $niceNames */
        $niceNames = [];
        foreach ($subscription->players()->withPivot('nice_name')->get() as $player) {
            /** @var Player&object{pivot: object{nice_name: string}} $player */
            $niceNames[(int) $player->steam_id] = $player->pivot->nice_name;
        }

        /** @var array<int, array<string, mixed>> $players */
        $players = $this->matchInfo['players'];

        $allPlayers = collect($players)
            /** @phpstan-ignore cast.int */
            ->sortBy(fn (array $player): int => (int) ($player['player_slot'] ?? 0))
            ->values()
            ->map(function (array $player) use ($niceNames): array {
                /** @var int $accountId */
                $accountId = $player['account_id'];

                /** @var array<int, array<string, mixed>> $stats */
                $stats = $player['stats'] ?? [];

                /** @var array<string, mixed>|null $lastStat */
                $lastStat = collect($stats)->last();

                return [
                    'account_id' => $accountId,
                    'hero_id' => $player['hero_id'] ?? null,
                    'team' => $player['team'] ?? null,
                    'display_name' => $niceNames[$accountId] ?? null,
                    'is_tracked' => isset($niceNames[$accountId]),
                    'kills' => $player['kills'] ?? 0,
                    'deaths' => $player['deaths'] ?? 0,
                    'assists' => $player['assists'] ?? 0,
                    'net_worth' => $player['net_worth'] ?? 0,
                    'player_damage' => $lastStat['player_damage'] ?? 0,
                    'player_healing' => $lastStat['player_healing'] ?? 0,
                    'last_hits' => $player['last_hits'] ?? 0,
                    'denies' => $player['denies'] ?? 0,
                ];
            });

        /**
         * @param  Collection<int, array<string, mixed>>  $players
         * @return array<int, array<string, mixed>>
         */
        $numberPlayers = function (Collection $players): array {
            /** @var array<int, array<string, mixed>> $result */
            $result = $players->values()->map(function (mixed $player, int $index): array {
                /** @var array<string, mixed> $player */
                $player['display_name'] ??= 'Player '.($index + 1);

                return $player;
            })->all();

            return $result;
        };

        $this->teamHiddenKing = $numberPlayers($allPlayers->slice(0, 6));
        $this->teamArchmother = $numberPlayers($allPlayers->slice(6, 6));
    }

    public function render(): View
    {
        return view('pages.matches.show');
    }
}
