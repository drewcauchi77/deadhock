<?php

declare(strict_types=1);

use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;

it('includes expected fields in toArray', function (): void {
    $subscription = Subscription::factory()->make();

    expect($subscription->toArray())->toHaveKeys(['guild_id', 'channel_id']);
});

it('belongs to many players', function (): void {
    $subscription = Subscription::factory()->create();
    $players = Player::factory(2)->create();
    $players->each(fn (Player $player) => $subscription->players()->attach($player->id, ['nice_name' => fake()->userName()]));

    expect($subscription->players)->toHaveCount(2)
        ->each->toBeInstanceOf(Player::class);
});

it('has many match posts', function (): void {
    $subscription = Subscription::factory()->create();
    MatchPost::factory(3)->create(['subscription_id' => $subscription->id]);

    expect($subscription->matchPosts)->toHaveCount(3)
        ->each->toBeInstanceOf(MatchPost::class);
});
