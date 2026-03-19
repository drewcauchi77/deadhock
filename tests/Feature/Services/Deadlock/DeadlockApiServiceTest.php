<?php

declare(strict_types=1);

use App\Services\Deadlock\DeadlockApiService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

function makeDeadlockService(array $responses, array &$history = []): DeadlockApiService
{
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    return new DeadlockApiService(new Client(['handler' => $stack]));
}

it('returns decoded json on successful response', function (): void {
    $history = [];
    $service = makeDeadlockService([
        new Response(200, [], json_encode(['foo' => 'bar'])),
    ], $history);

    $result = $service->get('test/path');

    expect($result)->toBe(['foo' => 'bar'])
        ->and($history)->toHaveCount(1)
        ->and((string) $history[0]['request']->getUri())->toContain('test/path');
});

it('returns null on request failure', function (): void {
    Log::shouldReceive('error')->once()->with(
        'Deadlock API request failed',
        Mockery::subset(['path' => 'test/path']),
    );

    $service = makeDeadlockService([
        new RequestException('timeout', new Request('GET', 'test')),
    ]);

    $result = $service->get('test/path');

    expect($result)->toBeNull();
});

it('returns null on invalid json response', function (): void {
    Log::shouldReceive('error')->once()->with(
        'Deadlock API request failed',
        Mockery::subset(['path' => 'test/path']),
    );

    $service = makeDeadlockService([
        new Response(200, [], 'not json'),
    ]);

    $result = $service->get('test/path');

    expect($result)->toBeNull();
});
