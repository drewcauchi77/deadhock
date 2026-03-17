<?php

declare(strict_types=1);

function signedDiscordRequest(string $body, string $privateKey): array
{
    $timestamp = (string) time();
    $signature = sodium_crypto_sign_detached($timestamp.$body, $privateKey);

    return [
        'headers' => [
            'X-Signature-Ed25519' => bin2hex($signature),
            'X-Signature-Timestamp' => $timestamp,
        ],
        'body' => $body,
    ];
}

beforeEach(function (): void {
    $keypair = sodium_crypto_sign_keypair();
    $this->privateKey = sodium_crypto_sign_secretkey($keypair);
    $publicKey = sodium_crypto_sign_publickey($keypair);

    config(['discord.public_key' => bin2hex($publicKey)]);
});

it('rejects requests with no signature header', function (): void {
    $this->postJson('/discord/interactions', ['type' => 1], [
        'X-Signature-Timestamp' => (string) time(),
    ])->assertUnauthorized();
});

it('rejects requests with no timestamp header', function (): void {
    $this->postJson('/discord/interactions', ['type' => 1], [
        'X-Signature-Ed25519' => str_repeat('a', 128),
    ])->assertUnauthorized();
});

it('rejects requests with an invalid signature', function (): void {
    $this->postJson('/discord/interactions', ['type' => 1], [
        'X-Signature-Ed25519' => str_repeat('a', 128),
        'X-Signature-Timestamp' => (string) time(),
    ])->assertUnauthorized();
});

it('passes through requests with a valid signature', function (): void {
    $body = json_encode(['type' => 1]);
    $signed = signedDiscordRequest((string) $body, $this->privateKey);

    $this->call('POST', '/discord/interactions', [], [], [], [
        'HTTP_X-Signature-Ed25519' => $signed['headers']['X-Signature-Ed25519'],
        'HTTP_X-Signature-Timestamp' => $signed['headers']['X-Signature-Timestamp'],
        'CONTENT_TYPE' => 'application/json',
    ], $signed['body'])->assertSuccessful();
});
