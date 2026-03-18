<?php

declare(strict_types=1);

namespace App\DTO;

final class SubscriptionDTO
{
    public function __construct(
        public string $guild_id,
        public string $channel_id,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'guild_id' => $this->guild_id,
            'channel_id' => $this->channel_id,
        ];
    }
}
