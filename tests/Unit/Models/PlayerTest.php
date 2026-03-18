<?php

declare(strict_types=1);

use App\Models\MatchPlayer;
use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;

it('includes expected fields in toArray', function (): void {
    $player = Player::factory()->make();

    expect($player->toArray())->toHaveKeys(['steam_id', 'last_checked_at']);
});

it('has null last_checked_at when unchecked', function (): void {
    $player = Player::factory()->unchecked()->make();

    expect($player->toArray())->toMatchArray(['last_checked_at' => null]);
});

it('belongs to many subscriptions', function (): void {
    $player = Player::factory()->create();
    $subscriptions = Subscription::factory(2)->create();
    $subscriptions->each(fn (Subscription $subscription) => $subscription->players()->attach($player->id, ['nice_name' => fake()->userName()]));

    expect($player->subscriptions)->toHaveCount(2)
        ->each->toBeInstanceOf(Subscription::class);
});

it('has many match players', function (): void {
    $player = Player::factory()->create();
    MatchPlayer::factory(3)->create(['player_id' => $player->id]);

    expect($player->matchPlayers)->toHaveCount(3)
        ->each->toBeInstanceOf(MatchPlayer::class);
});

it('has many match posts', function (): void {
    $player = Player::factory()->create();
    MatchPost::factory(2)->create(['player_id' => $player->id]);

    expect($player->matchPosts)->toHaveCount(2)
        ->each->toBeInstanceOf(MatchPost::class);
});
