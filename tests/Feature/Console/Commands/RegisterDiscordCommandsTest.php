<?php

declare(strict_types=1);

use App\Services\Discord\DiscordBotService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

function makeCommandService(array &$history): DiscordBotService
{
    $mock = new MockHandler([new Response(200), new Response(200)]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    return new DiscordBotService(new Client(['handler' => $stack]));
}

beforeEach(function (): void {
    config([
        'discord.api_base' => 'https://discord.test/api',
        'discord.app_id' => 'app-123',
        'discord.token' => 'bot-token',
    ]);
});

it('registers a global command and outputs success message', function (): void {
    $history = [];
    $this->instance(DiscordBotService::class, makeCommandService($history));

    $this->artisan('discord:register-commands')
        ->expectsOutput('Slash command registered globally (propagates within 1 hour).')
        ->assertSuccessful();

    expect((string) $history[0]['request']->getUri())
        ->toBe('https://discord.test/api/applications/app-123/commands');
});

it('registers a guild command when --guild option is provided', function (): void {
    $history = [];
    $this->instance(DiscordBotService::class, makeCommandService($history));

    $this->artisan('discord:register-commands', ['--guild' => 'guild-123'])
        ->expectsOutput('Slash command registered to guild guild-123 (instant).')
        ->assertSuccessful();

    expect((string) $history[0]['request']->getUri())
        ->toBe('https://discord.test/api/applications/app-123/guilds/guild-123/commands');
});
