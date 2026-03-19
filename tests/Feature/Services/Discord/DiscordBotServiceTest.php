<?php

declare(strict_types=1);

use App\Services\Discord\DiscordBotService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

function makeServiceWithHistory(array $responses, array &$history): DiscordBotService
{
    $mock = new MockHandler($responses);
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

it('registers a global command to the correct url', function (): void {
    $history = [];
    $service = makeServiceWithHistory([new Response(200)], $history);

    $service->registerGlobalCommand();

    expect($history)->toHaveCount(1)
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://discord.test/api/applications/app-123/commands');
});

it('registers a guild command to the correct url', function (): void {
    $history = [];
    $service = makeServiceWithHistory([new Response(200)], $history);

    $service->registerGuildCommand('guild-456');

    expect($history)->toHaveCount(1)
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://discord.test/api/applications/app-123/guilds/guild-456/commands');
});

it('sends the correct authorization header', function (): void {
    $history = [];
    $service = makeServiceWithHistory([new Response(200)], $history);

    $service->registerGlobalCommand();

    expect($history[0]['request']->getHeaderLine('Authorization'))->toBe('Bot bot-token');
});

it('logs an error when the global command request fails', function (): void {
    Log::shouldReceive('error')
        ->once()
        ->with('Failed to register global command', Mockery::subset(['error' => 'connection error']));

    $history = [];
    $service = makeServiceWithHistory([
        new RequestException('connection error', new Request('POST', 'test')),
    ], $history);

    $service->registerGlobalCommand();
});

it('logs an error with guild context when the guild command request fails', function (): void {
    Log::shouldReceive('error')
        ->once()
        ->with('Failed to register guild command', Mockery::subset(['guild_id' => 'guild-456']));

    $history = [];
    $service = makeServiceWithHistory([
        new RequestException('connection error', new Request('POST', 'test')),
    ], $history);

    $service->registerGuildCommand('guild-456');
});

it('posts match screenshot to the correct channel url', function (): void {
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_screenshot');
    file_put_contents($tmpFile, 'fake image');

    $history = [];
    $service = makeServiceWithHistory([new Response(200)], $history);

    $service->postMatchToChannel('channel-789', $tmpFile, 'Ace won a game.');

    expect($history)->toHaveCount(1)
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://discord.test/api/channels/channel-789/messages')
        ->and($history[0]['request']->getHeaderLine('Authorization'))
        ->toBe('Bot bot-token');

    unlink($tmpFile);
});

it('logs an error when posting match to channel fails', function (): void {
    $tmpFile = tempnam(sys_get_temp_dir(), 'test_screenshot');
    file_put_contents($tmpFile, 'fake image');

    Log::shouldReceive('error')
        ->once()
        ->with('Failed to send match result to Discord', Mockery::subset([
            'channel_id' => 'channel-789',
        ]));

    $history = [];
    $service = makeServiceWithHistory([
        new RequestException('connection error', new Request('POST', 'test')),
    ], $history);

    $service->postMatchToChannel('channel-789', $tmpFile, 'Ace won a game.');

    unlink($tmpFile);
});
