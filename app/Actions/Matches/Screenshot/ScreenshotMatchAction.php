<?php

declare(strict_types=1);

namespace App\Actions\Matches\Screenshot;

use App\Models\Matches;
use App\Models\Subscription;

final class ScreenshotMatchAction
{
    use CreateScreenshotAction;

    public function handle(Matches $match, Subscription $subscription): string
    {
        return $this->screenshot($match, $subscription, 'matches.show', 'match', 1248, 1106);
    }
}
