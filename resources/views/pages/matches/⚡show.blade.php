<?php

use Illuminate\Support\Facades\Cache;
use Livewire\Component;

new class extends Component
{
    public string $matchId;

    /** @var array<string, mixed>|null */
    public ?array $match = null;

    public function mount(string $matchId): void
    {
        $this->matchId = $matchId;
        $this->match = Cache::get("match.{$matchId}");

        if ($this->match === null) {
            abort(404);
        }
    }
};
?>

<div>
    <h1>Match #{{ $matchId }}</h1>

    <p>Duration: {{ floor($match['match_info']['duration_s'] / 60) }}m {{ $match['match_info']['duration_s'] % 60 }}s</p>
    <p>Winning Team: {{ $match['match_info']['winning_team'] === 0 ? 'Team 0' : 'Team 1' }}</p>

    <h2>Players</h2>
    <ul>
        @foreach ($match['match_info']['players'] as $player)
            <li>Account {{ $player['account_id'] }} (Slot {{ $player['player_slot'] }})</li>
        @endforeach
    </ul>
</div>