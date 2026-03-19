<?php

declare(strict_types=1);

use App\Actions\Matches\CheckPlayerMatchesAction;
use App\Actions\Matches\FetchAndCacheMatchAction;
use App\Actions\Matches\PostMatchToSubscriptionsAction;
use App\Models\Matches;
use App\Models\Player;
use App\Models\Subscription;
use App\Services\Deadlock\PlayerApiService;

use function Pest\Laravel\mock;

it('does nothing when API returns null', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturnNull();

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldNotReceive('handle');

    resolve(CheckPlayerMatchesAction::class)->handle($player);
});

it('does nothing when API returns empty array', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([]);

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldNotReceive('handle');

    resolve(CheckPlayerMatchesAction::class)->handle($player);
});

it('only processes latest match on first poll', function (): void {
    $player = Player::factory()->unchecked()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 100],
            ['match_id' => 99],
            ['match_id' => 98],
        ]);

    $match = Matches::factory()->make(['match_id' => '100']);

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldReceive('handle')->once()->with('100')->andReturnUsing(function () use ($match): Matches {
        $match->save();

        return $match;
    });

    $mockPost = mock(PostMatchToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect($player->fresh()->last_checked_at)->not->toBeNull();
});

it('stops processing when encountering an existing match', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Matches::factory()->create(['match_id' => '200']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 201],
            ['match_id' => 200],
            ['match_id' => 199],
        ]);

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldReceive('handle')->once()->with('201')->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '201']));

    $mockPost = mock(PostMatchToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);
});

it('continues when fetch returns null for a match', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 301],
            ['match_id' => 300],
        ]);

    Matches::factory()->create(['match_id' => '300']);

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldReceive('handle')->once()->with('301')->andReturnNull();

    $mockPost = mock(PostMatchToSubscriptionsAction::class);
    $mockPost->shouldNotReceive('handle');

    resolve(CheckPlayerMatchesAction::class)->handle($player);
});

it('updates last_checked_at after processing', function (): void {
    $player = Player::factory()->unchecked()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 400],
        ]);

    mock(FetchAndCacheMatchAction::class)
        ->shouldReceive('handle')->once()->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '400']));

    mock(PostMatchToSubscriptionsAction::class)
        ->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect($player->fresh()->last_checked_at)->not->toBeNull();
});
