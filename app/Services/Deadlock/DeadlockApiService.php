<?php

declare(strict_types=1);

namespace App\Services\Deadlock;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;

final readonly class DeadlockApiService
{
    public function __construct(private Client $client = new Client([
        'base_uri' => 'https://api.deadlock-api.com/v1/',
        'timeout' => 10,
    ])) {}

    /** @return array<mixed>|null */
    public function get(string $path): ?array
    {
        try {
            $response = $this->client->get($path);

            /** @var array<mixed> */
            return json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (GuzzleException|JsonException $e) {
            Log::error('Deadlock API request failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
