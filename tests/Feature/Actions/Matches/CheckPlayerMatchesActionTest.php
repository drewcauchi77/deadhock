<?php

declare(strict_types=1);

use App\Actions\Matches\CheckPlayerMatchesAction;
use App\Actions\Matches\FetchAndCacheMatchAction;
use App\Actions\Matches\SendToSubscriptionsAction;
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

    $mockPost = mock(SendToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect($player->fresh()->last_checked_at)->not->toBeNull();
});

it('stops at existing match and only processes newer ones', function (): void {
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

    $mockPost = mock(SendToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);
});

it('posts matches oldest first', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Matches::factory()->create(['match_id' => '500']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 503],
            ['match_id' => 502],
            ['match_id' => 501],
            ['match_id' => 500],
        ]);

    /** @var list<string> $postOrder */
    $postOrder = [];

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldReceive('handle')->with('503')->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '503']));
    $mockFetch->shouldReceive('handle')->with('502')->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '502']));
    $mockFetch->shouldReceive('handle')->with('501')->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '501']));

    $mockPost = mock(SendToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->times(3)->andReturnUsing(function (Matches $match) use (&$postOrder): void {
        $postOrder[] = $match->match_id;
    });

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect($postOrder)->toBe(['501', '502', '503']);
});

it('creates a match record when fetch returns null to avoid retrying', function (): void {
    $player = Player::factory()->create();
    $subscription = Subscription::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    Matches::factory()->create(['match_id' => '299']);

    mock(PlayerApiService::class)
        ->shouldReceive('getMatchHistory')->once()->andReturn([
            ['match_id' => 301],
            ['match_id' => 300],
            ['match_id' => 299],
        ]);

    $mockFetch = mock(FetchAndCacheMatchAction::class);
    $mockFetch->shouldReceive('handle')->with('301')->andReturnNull();
    $mockFetch->shouldReceive('handle')->with('300')->andReturnUsing(fn (): Matches => Matches::factory()->create(['match_id' => '300']));

    $mockPost = mock(SendToSubscriptionsAction::class);
    $mockPost->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect(Matches::query()->where('match_id', '301')->exists())->toBeTrue();
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

    mock(SendToSubscriptionsAction::class)
        ->shouldReceive('handle')->once();

    resolve(CheckPlayerMatchesAction::class)->handle($player);

    expect($player->fresh()->last_checked_at)->not->toBeNull();
});
