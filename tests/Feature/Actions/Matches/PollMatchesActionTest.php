<?php

declare(strict_types=1);

use App\Actions\Matches\CheckPlayerMatchesAction;
use App\Actions\Matches\PollMatchesAction;
use App\Models\Player;
use App\Models\Subscription;

use function Pest\Laravel\mock;

it('calls check player matches for each player with subscriptions', function (): void {
    $subscription = Subscription::factory()->create();

    $playerWithSub = Player::factory()->create();
    $playerWithSub->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $playerWithoutSub = Player::factory()->create();

    $mockCheck = mock(CheckPlayerMatchesAction::class);
    $mockCheck->shouldReceive('handle')->once()->withArgs(fn (Player $player): bool => $player->id === $playerWithSub->id);

    resolve(PollMatchesAction::class)->handle();
});

it('does nothing when no players have subscriptions', function (): void {
    Player::factory()->create();

    $mockCheck = mock(CheckPlayerMatchesAction::class);
    $mockCheck->shouldNotReceive('handle');

    resolve(PollMatchesAction::class)->handle();
});
