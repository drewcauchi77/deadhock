<?php

declare(strict_types=1);

use App\Actions\Player\ParsePlayerAction;
use App\DTO\PlayerDTO;

it('parses a single player pair', function (): void {
    $players = new ParsePlayerAction()->handle('76561198000:TestPlayer');

    expect($players)->toHaveCount(1)
        ->and($players[0])->toBeInstanceOf(PlayerDTO::class)
        ->and($players[0]->steam_id)->toBe('76561198000')
        ->and($players[0]->nice_name)->toBe('TestPlayer');
});

it('parses multiple player pairs', function (): void {
    $players = new ParsePlayerAction()->handle('76561198000:PlayerOne 76561198001:PlayerTwo');

    expect($players)->toHaveCount(2)
        ->and($players[0]->steam_id)->toBe('76561198000')
        ->and($players[0]->nice_name)->toBe('PlayerOne')
        ->and($players[1]->steam_id)->toBe('76561198001')
        ->and($players[1]->nice_name)->toBe('PlayerTwo');
});

it('trims whitespace from input', function (): void {
    $players = new ParsePlayerAction()->handle('  76561198000:TestPlayer  ');

    expect($players)->toHaveCount(1)
        ->and($players[0]->steam_id)->toBe('76561198000');
});

it('skips empty groups from extra spaces', function (): void {
    $players = new ParsePlayerAction()->handle('76561198000:One   76561198001:Two');

    expect($players)->toHaveCount(2);
});

it('skips pairs with missing colon separator', function (): void {
    $players = new ParsePlayerAction()->handle('76561198000TestPlayer');

    expect($players)->toHaveCount(0);
});

it('skips pairs with too many colon segments', function (): void {
    $players = new ParsePlayerAction()->handle('765:test:extra');

    expect($players)->toHaveCount(0);
});

it('skips pairs with empty steam_id', function (): void {
    $players = new ParsePlayerAction()->handle(':TestPlayer');

    expect($players)->toHaveCount(0);
});

it('skips pairs with empty nice_name', function (): void {
    $players = new ParsePlayerAction()->handle('76561198000:');

    expect($players)->toHaveCount(0);
});

it('returns empty array for empty string', function (): void {
    $players = new ParsePlayerAction()->handle('');

    expect($players)->toHaveCount(0);
});

it('returns empty array for whitespace only string', function (): void {
    $players = new ParsePlayerAction()->handle('   ');

    expect($players)->toHaveCount(0);
});
