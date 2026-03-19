<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Player\ParsePlayerAction;
use App\Actions\Player\StorePlayerAction;
use App\Actions\PlayerSubscription\StorePlayerSubscriptionAction;
use App\Actions\Subscription\StoreSubscriptionAction;
use App\Actions\VerifyDiscordInteractionAction;
use App\DTO\SubscriptionDTO;
use App\Http\Requests\StoreSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final class DiscordInteractionController extends Controller
{
    public function invoke(
        StoreSubscriptionRequest $storeSubscriptionRequest,
        VerifyDiscordInteractionAction $verifyDiscordInteractionAction,
        StoreSubscriptionAction $storeSubscriptionAction,
        ParsePlayerAction $parsePlayerAction,
        StorePlayerAction $storePlayerAction,
        StorePlayerSubscriptionAction $storePlayerSubscriptionAction,
    ): JsonResponse {
        if (($response = $verifyDiscordInteractionAction->handle($storeSubscriptionRequest)) instanceof JsonResponse) {
            return $response;
        }

        $storedSubscription = $storeSubscriptionAction->handle(new SubscriptionDTO(
            $storeSubscriptionRequest->string('guild_id')->toString(),
            $storeSubscriptionRequest->string('channel_id')->toString(),
        ));

        $players = $parsePlayerAction->handle(
            $storeSubscriptionRequest->string('data.options.0.value')->toString(),
        );

        foreach ($players as $player) {
            $storedPlayer = $storePlayerAction->handle($player);
            $storePlayerSubscriptionAction->handle($storedSubscription, $storedPlayer, $player);
        }

        return response()->json([
            'type' => 4,
            'data' => [
                'content' => sprintf('Now tracking %d player(s) in this channel.', count($players)),
            ],
        ]);
    }
}
