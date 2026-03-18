<?php

declare(strict_types=1);

namespace App\Actions\PlayerSubscription;

use App\DTO\PlayerDTO;
use App\Models\Player;
use App\Models\PlayerSubscription;
use App\Models\Subscription;

final class StorePlayerSubscriptionAction
{
    public function handle(Subscription $subscription, Player $player, PlayerDTO $playerDTO): PlayerSubscription
    {
        return PlayerSubscription::query()->updateOrCreate([
            'subscription_id' => $subscription->id,
            'player_id' => $player->id,
        ], [
            'nice_name' => $playerDTO->nice_name,
        ]);
    }
}
