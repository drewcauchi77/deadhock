<?php

declare(strict_types=1);

namespace App\Actions\Player;

use App\DTO\PlayerDTO;

final class ParsePlayerAction
{
    /** @return array<int, PlayerDTO> */
    public function handle(string $pairs): array
    {
        $playerGroups = explode(' ', mb_trim($pairs));
        $players = [];

        foreach ($playerGroups as $playerGroup) {
            $playerGroup = mb_trim($playerGroup);

            if ($playerGroup === '') {
                continue;
            }

            $playerData = explode(':', $playerGroup);

            if (count($playerData) !== 2) {
                continue;
            }

            $steamId = mb_trim($playerData[0]);
            $niceName = mb_trim($playerData[1]);
            if ($steamId === '') {
                continue;
            }

            if ($niceName === '') {
                continue;
            }

            $players[] = new PlayerDTO($steamId, $niceName);
        }

        return $players;
    }
}
