<?php

declare(strict_types=1);

use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\Player;

it('includes expected fields in toArray', function (): void {
    $matchPlayer = MatchPlayer::factory()->make();

    expect($matchPlayer->toArray())->toHaveKeys(['match_id', 'player_id']);
});

it('belongs to a match', function (): void {
    $matchPlayer = MatchPlayer::factory()->create();

    expect($matchPlayer->match)->toBeInstanceOf(Matches::class)
        ->and($matchPlayer->match->id)->toBe($matchPlayer->match_id);
});

it('belongs to a player', function (): void {
    $matchPlayer = MatchPlayer::factory()->create();

    expect($matchPlayer->player)->toBeInstanceOf(Player::class)
        ->and($matchPlayer->player->id)->toBe($matchPlayer->player_id);
});
