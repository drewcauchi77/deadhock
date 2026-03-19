<?php

declare(strict_types=1);

use App\Actions\Matches\BuildMatchMessageAction;
use App\Actions\Matches\PostMatchToSubscriptionsAction;
use App\Actions\Matches\ScreenshotMatchAction;
use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\MatchPost;
use App\Models\Player;
use App\Models\Subscription;
use App\Services\Discord\DiscordBotService;

use function Pest\Laravel\mock;

it('posts match to subscriptions with tracked players', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create();
    MatchPlayer::factory()->create(['match_id' => $match->id, 'player_id' => $player->id]);

    mock(ScreenshotMatchAction::class)
        ->shouldReceive('handle')->once()->andReturn('/tmp/screenshot.png');

    mock(BuildMatchMessageAction::class)
        ->shouldReceive('handle')->once()->andReturn('Ace (playing as Infernus) won a game.');

    mock(DiscordBotService::class)
        ->shouldReceive('postMatchToChannel')->once()->withArgs(fn (string $channelId, string $path, string $message): bool => $channelId === $subscription->channel_id
            && $path === '/tmp/screenshot.png'
            && $message === 'Ace (playing as Infernus) won a game.');

    resolve(PostMatchToSubscriptionsAction::class)->handle($match);

    expect(MatchPost::query()->count())->toBe(1);
});

it('skips subscription that already has a match post', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create();
    MatchPlayer::factory()->create(['match_id' => $match->id, 'player_id' => $player->id]);

    MatchPost::factory()->create([
        'match_id' => $match->id,
        'subscription_id' => $subscription->id,
        'player_id' => $player->id,
    ]);

    mock(ScreenshotMatchAction::class)->shouldNotReceive('handle');
    mock(BuildMatchMessageAction::class)->shouldNotReceive('handle');
    mock(DiscordBotService::class)->shouldNotReceive('postMatchToChannel');

    resolve(PostMatchToSubscriptionsAction::class)->handle($match);

    expect(MatchPost::query()->count())->toBe(1);
});

it('does not post to subscriptions without tracked players in the match', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create();

    mock(ScreenshotMatchAction::class)->shouldNotReceive('handle');
    mock(BuildMatchMessageAction::class)->shouldNotReceive('handle');
    mock(DiscordBotService::class)->shouldNotReceive('postMatchToChannel');

    resolve(PostMatchToSubscriptionsAction::class)->handle($match);

    expect(MatchPost::query()->count())->toBe(0);
});

it('handles race condition when match post is created between exists check and insert', function (): void {
    $subscription = Subscription::factory()->create();
    $player = Player::factory()->create();
    $player->subscriptions()->attach($subscription, ['nice_name' => 'Ace']);

    $match = Matches::factory()->create();
    MatchPlayer::factory()->create(['match_id' => $match->id, 'player_id' => $player->id]);

    mock(ScreenshotMatchAction::class)
        ->shouldReceive('handle')->once()->andReturnUsing(function () use ($match, $subscription, $player): string {
            MatchPost::factory()->create([
                'match_id' => $match->id,
                'subscription_id' => $subscription->id,
                'player_id' => $player->id,
            ]);

            return '/tmp/screenshot.png';
        });

    mock(BuildMatchMessageAction::class)
        ->shouldReceive('handle')->once()->andReturn('Ace won a game.');

    mock(DiscordBotService::class)
        ->shouldReceive('postMatchToChannel')->once();

    resolve(PostMatchToSubscriptionsAction::class)->handle($match);

    expect(MatchPost::query()->count())->toBe(1);
});

it('posts to multiple subscriptions with shared players', function (): void {
    $sub1 = Subscription::factory()->create();
    $sub2 = Subscription::factory()->create();
    $player = Player::factory()->create();
    $player->subscriptions()->attach($sub1, ['nice_name' => 'Ace']);
    $player->subscriptions()->attach($sub2, ['nice_name' => 'Bromar']);

    $match = Matches::factory()->create();
    MatchPlayer::factory()->create(['match_id' => $match->id, 'player_id' => $player->id]);

    mock(ScreenshotMatchAction::class)
        ->shouldReceive('handle')->twice()->andReturn('/tmp/screenshot.png');

    mock(BuildMatchMessageAction::class)
        ->shouldReceive('handle')->twice()->andReturn('Player won a game.');

    mock(DiscordBotService::class)
        ->shouldReceive('postMatchToChannel')->twice();

    resolve(PostMatchToSubscriptionsAction::class)->handle($match);

    expect(MatchPost::query()->count())->toBe(2);
});
