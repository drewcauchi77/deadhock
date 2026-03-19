<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Models\Player;

final readonly class PollMatchesAction
{
    public function __construct(private CheckPlayerMatchesAction $checkPlayerMatchesAction) {}

    public function handle(): void
    {
        $players = Player::query()->whereHas('subscriptions')->with('subscriptions')->get();

        foreach ($players as $player) {
            $this->checkPlayerMatchesAction->handle($player);
        }
    }
}
