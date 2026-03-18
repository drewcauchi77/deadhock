<?php

declare(strict_types=1);

use App\DTO\PlayerDTO;

it('stores steam_id and nice_name on construction', function (): void {
    $dto = new PlayerDTO(steam_id: '76561198000', nice_name: 'TestPlayer');

    expect($dto->steam_id)->toBe('76561198000')
        ->and($dto->nice_name)->toBe('TestPlayer');
});

it('returns correct array shape from toArray', function (): void {
    $dto = new PlayerDTO(steam_id: '76561198000', nice_name: 'TestPlayer');

    expect($dto->toArray())->toBe([
        'steam_id' => '76561198000',
        'nice_name' => 'TestPlayer',
    ]);
});
