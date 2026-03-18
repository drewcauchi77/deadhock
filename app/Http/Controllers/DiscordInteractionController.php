<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DiscordSubscription\StoreSubscriptionAction;
use App\Actions\VerifyDiscordInteractionAction;
use App\DTO\SubscriptionDTO;
use App\Http\Requests\StoreSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final class DiscordInteractionController extends Controller
{
    public function invoke(
        StoreSubscriptionRequest $storeDiscordSubscriptionRequest,
        VerifyDiscordInteractionAction $verifyDiscordInteractionAction,
        StoreSubscriptionAction $createDiscordSubscriptionAction,
    ): JsonResponse {
        if (($response = $verifyDiscordInteractionAction->handle($storeDiscordSubscriptionRequest)) instanceof JsonResponse) {
            return $response;
        }

        $createDiscordSubscriptionAction->handle(new SubscriptionDTO(
            $storeDiscordSubscriptionRequest->string('channel_id')->toString(),
            $storeDiscordSubscriptionRequest->string('guild_id')->toString(),
        ));

        return response()->json(['test' => true]);
    }
}
