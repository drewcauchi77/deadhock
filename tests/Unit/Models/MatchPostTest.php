<?php

declare(strict_types=1);

use App\Models\Matches;
use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;

it('includes expected fields in toArray', function (): void {
    $matchPost = MatchPost::factory()->make();

    expect($matchPost->toArray())->toHaveKeys([
        'match_id',
        'player_id',
        'subscription_id',
        'discord_message_id',
        'posted_at',
    ]);
});

it('belongs to a match', function (): void {
    $matchPost = MatchPost::factory()->create();

    expect($matchPost->match)->toBeInstanceOf(Matches::class)
        ->and($matchPost->match->id)->toBe($matchPost->match_id);
});

it('belongs to a player', function (): void {
    $matchPost = MatchPost::factory()->create();

    expect($matchPost->player)->toBeInstanceOf(Player::class)
        ->and($matchPost->player->id)->toBe($matchPost->player_id);
});

it('belongs to a subscription', function (): void {
    $matchPost = MatchPost::factory()->create();

    expect($matchPost->subscription)->toBeInstanceOf(Subscription::class)
        ->and($matchPost->subscription->id)->toBe($matchPost->subscription_id);
});
