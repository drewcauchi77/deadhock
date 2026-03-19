<?php

declare(strict_types=1);

namespace App\Services\Discord;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

final readonly class DiscordBotService
{
    public function __construct(private Client $client = new Client()) {}

    public function registerGlobalCommand(): void
    {
        $apiBase = config('discord.api_base');
        $appId = config('discord.app_id');
        assert(is_string($apiBase) && is_string($appId));

        $this->postCommand(
            sprintf('%s/applications/%s/commands', $apiBase, $appId),
            'Failed to register global command',
        );
    }

    public function registerGuildCommand(string $guildId): void
    {
        $apiBase = config('discord.api_base');
        $appId = config('discord.app_id');
        assert(is_string($apiBase) && is_string($appId));

        $this->postCommand(
            sprintf('%s/applications/%s/guilds/%s/commands', $apiBase, $appId, $guildId),
            'Failed to register guild command',
            ['guild_id' => $guildId],
        );
    }

    public function postMatchToChannel(string $channelId, string $screenshotPath, string $messageContent): void
    {
        $apiBase = config('discord.api_base');
        $token = config('discord.token');
        assert(is_string($apiBase) && is_string($token));

        try {
            $this->client->post(
                sprintf('%s/channels/%s/messages', $apiBase, $channelId),
                [
                    'headers' => [
                        'Authorization' => 'Bot '.$token,
                    ],
                    'multipart' => [
                        [
                            'name' => 'content',
                            'contents' => $messageContent,
                        ],
                        [
                            'name' => 'files[0]',
                            'contents' => fopen($screenshotPath, 'r'),
                            'filename' => 'match.png',
                        ],
                    ],
                ],
            );
        } catch (GuzzleException $guzzleException) {
            Log::error('Failed to send match result to Discord', [
                'channel_id' => $channelId,
                'error' => $guzzleException->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, string>  $logContext
     */
    private function postCommand(string $url, string $errorMessage, array $logContext = []): void
    {
        $token = config('discord.token');
        assert(is_string($token));

        try {
            $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'Bot '.$token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => 'deadhock',
                    'description' => 'Track Deadlock players and receive post-match screenshots in this channel',
                    'options' => [
                        [
                            'type' => 3,
                            'name' => 'players',
                            'description' => 'Space-separated steamId:niceName pairs (e.g. 1234567890:Ace 9876543210:Bromar)',
                            'required' => true,
                        ],
                    ],
                ],
            ]);
        } catch (GuzzleException $guzzleException) {
            Log::error($errorMessage, [...$logContext, 'error' => $guzzleException->getMessage()]);
        }
    }
}
