<?php

declare(strict_types=1);

use App\Http\Middleware\VerifyDiscordSignature;
use App\Models\Subscription;

beforeEach(function (): void {
    $this->withoutMiddleware(VerifyDiscordSignature::class);
});

it('returns a ping response when type is 1', function (): void {
    $this->postJson('/discord/interactions', ['type' => 1])
        ->assertSuccessful()
        ->assertExactJson(['type' => 1]);
});

it('creates a subscription and returns success for a subscription interaction', function (): void {
    $this->postJson('/discord/interactions', [
        'type' => 2,
        'guild_id' => 'guild-123',
        'channel_id' => 'channel-456',
        'data' => [
            'options' => [
                ['value' => '76561198000:TestPlayer'],
            ],
        ],
    ])
        ->assertSuccessful()
        ->assertJsonFragment(['type' => 4]);

    expect(Subscription::query()->count())->toBe(1);
});
