<?php

declare(strict_types=1);

use App\Actions\DiscordSubscription\CreateDiscordSubscriptionAction;
use App\DTO\DiscordSubscriptionDTO;
use App\Models\DiscordSubscription;

it('creates a new discord subscription', function (): void {
    $dto = new DiscordSubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');

    $subscription = new CreateDiscordSubscriptionAction()->handle($dto);

    expect($subscription)
        ->toBeInstanceOf(DiscordSubscription::class)
        ->and($subscription->guild_id)->toBe('guild-123')
        ->and($subscription->channel_id)->toBe('channel-456');
});

it('returns existing subscription instead of creating a duplicate', function (): void {
    $dto = new DiscordSubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');
    $action = new CreateDiscordSubscriptionAction();

    $first = $action->handle($dto);
    $second = $action->handle($dto);

    expect(DiscordSubscription::query()->count())->toBe(1)
        ->and($second->getKey())->toBe($first->getKey());
});
