<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Matches;

use App\Models\Subscription;
use Illuminate\Contracts\View\View;
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
        $match = Cache::get('match.'.$matchId);

        abort_if($match === null, 404);

        $this->matchInfo = $match['match_info'];

        // @note https://assets.deadlock-api.com/v2/heroes
        $heroData = json_decode(File::get(database_path('data/heroes.json')), true);
        $this->heroes = collect($heroData)->keyBy('id')->all();

        $niceNames = [];
        foreach ($subscription->players()->withPivot('nice_name')->get() as $player) {
            $niceNames[(int) $player->steam_id] = $player->pivot->nice_name;
        }

        $allPlayers = collect($this->matchInfo['players'])
            ->map(function (array $player) use ($niceNames): array {
                $accountId = $player['account_id'];
                $lastStat = collect($player['stats'] ?? [])->last();

                return [
                    'account_id' => $accountId,
                    'hero_id' => $player['hero_id'] ?? null,
                    'team' => $player['team'] ?? null,
                    'display_name' => $niceNames[$accountId] ?? null,
                    'is_tracked' => isset($niceNames[$accountId]),
                    'kills' => $player['kills'] ?? 0,
                    'deaths' => $player['deaths'] ?? 0,
                    'assists' => $player['assists'] ?? 0,
                    'player_damage' => $lastStat['player_damage'] ?? 0,
                    'player_healing' => $lastStat['player_healing'] ?? 0,
                    'last_hits' => $player['last_hits'] ?? 0,
                    'denies' => $player['denies'] ?? 0,
                ];
            });

        $numberPlayers = fn ($players) => $players->values()->map(function (array $player, int $index): array {
            $player['display_name'] ??= 'Player '.($index + 1);

            return $player;
        })->all();

        $this->teamArchmother = $numberPlayers($allPlayers->slice(0, 6));
        $this->teamHiddenKing = $numberPlayers($allPlayers->slice(6, 6));
    }

    public function render(): View
    {
        return view('pages.matches.show');
    }
}
