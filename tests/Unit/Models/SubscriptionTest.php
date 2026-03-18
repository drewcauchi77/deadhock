<?php

declare(strict_types=1);

use App\Models\Subscription;

it('includes expected fields in toArray', function (): void {
    $subscription = Subscription::factory()->make();

    expect($subscription->toArray())->toHaveKeys(['guild_id', 'channel_id']);
});
