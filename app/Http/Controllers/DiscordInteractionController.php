<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\VerifyDiscordInteractionAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class DiscordInteractionController extends Controller
{
    public function invoke(Request $request, VerifyDiscordInteractionAction $verifyDiscordInteractionAction): JsonResponse
    {
        if (($response = $verifyDiscordInteractionAction->handle($request)) instanceof JsonResponse) {
            return $response;
        }

        return response()->json(['test' => true]);
    }
}
