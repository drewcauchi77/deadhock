<?php

declare(strict_types=1);

use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\MatchPost;

it('includes expected fields in toArray', function (): void {
    $match = Matches::factory()->make();

    expect($match->toArray())->toHaveKeys(['match_id', 'match_started_at', 'fetched_at']);
});

it('has many match players', function (): void {
    $match = Matches::factory()->create();
    MatchPlayer::factory(3)->create(['match_id' => $match->id]);

    expect($match->matchPlayers)->toHaveCount(3)
        ->each->toBeInstanceOf(MatchPlayer::class);
});

it('has many match posts', function (): void {
    $match = Matches::factory()->create();
    MatchPost::factory(2)->create(['match_id' => $match->id]);

    expect($match->matchPosts)->toHaveCount(2)
        ->each->toBeInstanceOf(MatchPost::class);
});
