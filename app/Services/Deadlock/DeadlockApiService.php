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

    /**
     * @return array<mixed>|null
     */
    public function get(string $path, int $value): null
    {
        try {
            $response = $this->client->get($path);

            $decoded = json_decode(
                json: $response->getBody()->getContents(),
                associative: true,
                flags: JSON_THROW_ON_ERROR,
            );

            Log::info('Deadlock API request successful '.$value, $decoded);

            return null;
        } catch (GuzzleException|JsonException $e) {
            Log::error('Deadlock API request failed '.$value, [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
