<?php

declare(strict_types=1);

namespace App\DTO;

final class PlayerDTO
{
    public function __construct(
        public string $steam_id,
        public string $nice_name,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'steam_id' => $this->steam_id,
            'nice_name' => $this->nice_name,
        ];
    }
}
