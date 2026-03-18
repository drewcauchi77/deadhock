<?php

declare(strict_types=1);

use App\DTO\SubscriptionDTO;

it('stores guild_id and channel_id on construction', function (): void {
    $dto = new SubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');

    expect($dto->guild_id)->toBe('guild-123')
        ->and($dto->channel_id)->toBe('channel-456');
});

it('returns correct array shape from toArray', function (): void {
    $dto = new SubscriptionDTO(guild_id: 'guild-123', channel_id: 'channel-456');

    expect($dto->toArray())->toBe([
        'guild_id' => 'guild-123',
        'channel_id' => 'channel-456',
    ]);
});
