<?php

declare(strict_types=1);

use App\Actions\DiscordSubscription\StoreSubscriptionAction;
use App\DTO\SubscriptionDTO;
use App\Models\Subscription;

it('creates a new discord subscription', function (): void {
    $dto = new SubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');

    $subscription = new StoreSubscriptionAction()->handle($dto);

    expect($subscription)
        ->toBeInstanceOf(Subscription::class)
        ->and($subscription->guild_id)->toBe('guild-123')
        ->and($subscription->channel_id)->toBe('channel-456');
});

it('returns existing subscription instead of creating a duplicate', function (): void {
    $dto = new SubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');
    $action = new StoreSubscriptionAction();

    $first = $action->handle($dto);
    $second = $action->handle($dto);

    expect(Subscription::query()->count())->toBe(1)
        ->and($second->getKey())->toBe($first->getKey());
});
