<?php

declare(strict_types=1);

namespace App\Actions\Discord;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class VerifyDiscordInteractionAction
{
    public function handle(Request $request): ?JsonResponse
    {
        $type = $request->integer('type');

        if ($type === 1) {
            return response()->json(['type' => 1]);
        }

        return null;
    }
}
