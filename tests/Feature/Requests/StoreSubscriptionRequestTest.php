<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyDiscordSignature;

beforeEach(function (): void {
    $this->withoutMiddleware(VerifyDiscordSignature::class);
});

it('requires guild_id and channel_id for non-ping interactions', function (): void {
    $this->postJson('/discord/interactions', ['type' => 2])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['guild_id', 'channel_id']);
});

it('does not require guild_id and channel_id for ping interactions', function (): void {
    $this->postJson('/discord/interactions', ['type' => 1])
        ->assertSuccessful();
});

it('rejects non-string guild_id', function (): void {
    $this->postJson('/discord/interactions', ['type' => 2, 'guild_id' => 123, 'channel_id' => 'channel-456'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['guild_id']);
});

it('rejects non-string channel_id', function (): void {
    $this->postJson('/discord/interactions', ['type' => 2, 'guild_id' => 'guild-123', 'channel_id' => 123])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['channel_id']);
});
