<?php

declare(strict_types=1);

namespace App\Actions\DiscordSubscription;

use App\DTO\SubscriptionDTO;
use App\Models\Subscription;

final class StoreSubscriptionAction
{
    public function handle(SubscriptionDTO $data): Subscription
    {
        return Subscription::query()->firstOrCreate((array) $data);
    }
}
