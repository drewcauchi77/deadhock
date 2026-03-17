<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DiscordSubscription\CreateDiscordSubscriptionAction;
use App\Actions\VerifyDiscordInteractionAction;
use App\DTO\DiscordSubscriptionDTO;
use App\Http\Requests\StoreDiscordSubscriptionRequest;
use Illuminate\Http\JsonResponse;

final class DiscordInteractionController extends Controller
{
    public function invoke(
        StoreDiscordSubscriptionRequest $storeDiscordSubscriptionRequest,
        VerifyDiscordInteractionAction $verifyDiscordInteractionAction,
        CreateDiscordSubscriptionAction $createDiscordSubscriptionAction
    ): JsonResponse {
        if (($response = $verifyDiscordInteractionAction->handle($storeDiscordSubscriptionRequest)) instanceof JsonResponse) {
            return $response;
        }

        $createDiscordSubscriptionAction->handle(new DiscordSubscriptionDTO(
            $storeDiscordSubscriptionRequest->string('channel_id')->toString(),
            $storeDiscordSubscriptionRequest->string('guild_id')->toString(),
        ));

        return response()->json(['test' => true]);
    }
}
