<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyDiscordSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp = $request->header('X-Signature-Timestamp');

        abort_if(! is_string($signature) || ! is_string($timestamp), 401);

        $signatureBytes = hex2bin($signature);
        $publicKey = config('discord.public_key');

        abort_if($signatureBytes === false || $signatureBytes === '' || ! is_string($publicKey), 401);

        $publicKeyBytes = hex2bin((string) $publicKey);

        abort_if($publicKeyBytes === false || $publicKeyBytes === '', 401);

        $isValid = sodium_crypto_sign_verify_detached(
            $signatureBytes,
            $timestamp.$request->getContent(),
            $publicKeyBytes,
        );

        abort_unless($isValid, 401);

        $response = $next($request);
        assert($response instanceof Response);

        return $response;
    }
}
