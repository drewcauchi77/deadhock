<?php

declare(strict_types=1);

namespace App\Actions\DiscordSubscription;

use App\DTO\DiscordSubscriptionDTO;
use App\Models\DiscordSubscription;

final class CreateDiscordSubscriptionAction
{
    public function handle(DiscordSubscriptionDTO $data): DiscordSubscription
    {
        return DiscordSubscription::query()->firstOrCreate((array) $data);
    }
}
