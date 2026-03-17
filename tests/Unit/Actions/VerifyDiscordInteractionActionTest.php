<?php

declare(strict_types=1);

use App\Actions\VerifyDiscordInteractionAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

it('returns a ping response when type is 1', function (): void {
    $request = Request::create('/', 'POST', ['type' => 1]);

    $response = new VerifyDiscordInteractionAction()->handle($request);

    expect($response)
        ->toBeInstanceOf(JsonResponse::class)
        ->and($response->getData(true))->toBe(['type' => 1]);
});

it('returns null for non-ping interaction types', function (): void {
    $request = Request::create('/', 'POST', ['type' => 2]);

    $response = new VerifyDiscordInteractionAction()->handle($request);

    expect($response)->toBeNull();
});
