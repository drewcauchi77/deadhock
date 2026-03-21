<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Matches;

use App\Models\Player;
use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Livewire\Component;

final class Mvp extends Component
{
    public string $matchId;

    /** @var array<int, array<string, mixed>> */
    public array $mvpPlayers = [];

    /** @var array<int, array<string, mixed>> */
    public array $heroes = [];

    public ?int $winningTeam = null;

    public function mount(string $matchId, Subscription $subscription): void
    {
        $this->matchId = $matchId;

        /** @var array{match_info: array<string, mixed>}|null $match */
        $match = Cache::get('match.'.$matchId);

        abort_if($match === null, 404);

        /** @var array<string, mixed> $matchInfo */
        $matchInfo = $match['match_info'];

        /** @var int|null $winTeam */
        $winTeam = $matchInfo['winning_team'] ?? null;
        $this->winningTeam = $winTeam !== null ? (int) $winTeam : null;

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
        $players = $matchInfo['players'];

        $this->mvpPlayers = collect($players)
            ->filter(fn (array $player): bool => isset($player['mvp_rank']) && in_array($player['mvp_rank'], [1, 2, 3], true))
            ->sortBy(fn (array $player): int => (int) $player['mvp_rank'])
            ->values()
            ->map(function (array $player) use ($niceNames): array {
                /** @var int $accountId */
                $accountId = $player['account_id'];

                $isTracked = isset($niceNames[$accountId]);
                /** @var int|null $heroId */
                $heroId = $player['hero_id'] ?? null;
                $heroName = $heroId !== null && isset($this->heroes[$heroId]) ? $this->heroes[$heroId]['name'] : 'Unknown';

                return [
                    'account_id' => $accountId,
                    'hero_id' => $heroId,
                    'hero_name' => $heroName,
                    'display_name' => $isTracked ? $niceNames[$accountId] : $heroName,
                    'is_tracked' => $isTracked,
                    'mvp_rank' => (int) $player['mvp_rank'],
                    'team' => $player['team'] ?? null,
                    'kills' => $player['kills'] ?? 0,
                    'deaths' => $player['deaths'] ?? 0,
                    'assists' => $player['assists'] ?? 0,
                    'net_worth' => $player['net_worth'] ?? 0,
                ];
            })
            ->all();
    }

    public function render(): View
    {
        return view('pages.matches.mvp');
    }
}
