<?php

declare(strict_types=1);

use App\Models\DiscordSubscription;

it('includes expected fields in toArray', function (): void {
    $subscription = DiscordSubscription::factory()->make();

    expect($subscription->toArray())->toHaveKeys(['guild_id', 'channel_id']);
});
