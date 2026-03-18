<?php

declare(strict_types=1);

use App\Models\Player;

it('includes expected fields in toArray', function (): void {
    $player = Player::factory()->make();

    expect($player->toArray())->toHaveKeys(['steam_id', 'last_checked_at']);
});

it('has null last_checked_at when unchecked', function (): void {
    $player = Player::factory()->unchecked()->make();

    expect($player->toArray())->toMatchArray(['last_checked_at' => null]);
});
