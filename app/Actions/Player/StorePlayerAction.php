<?php

declare(strict_types=1);

namespace App\Actions\Player;

use App\DTO\PlayerDTO;
use App\Models\Player;

final class StorePlayerAction
{
    public function handle(PlayerDTO $playerDTO): Player
    {
        return Player::query()->createOrFirst([
            'steam_id' => $playerDTO->steam_id,
        ]);
    }
}
