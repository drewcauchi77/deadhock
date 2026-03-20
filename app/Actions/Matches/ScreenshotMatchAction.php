<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Models\Matches;
use App\Models\Subscription;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

final class ScreenshotMatchAction
{
    public function handle(Matches $match, Subscription $subscription): string
    {
        $url = route('matches.show', [
            'matchId' => $match->match_id,
            'subscription' => $subscription->id,
        ]);

        $directory = storage_path('app/private/screenshots');
        File::ensureDirectoryExists($directory);

        $path = sprintf('%s/match_%s_%s.png', $directory, $match->match_id, $subscription->id);

        $this->createBrowsershot($url)
            ->windowSize(1248, 1056)
            ->save($path);

        return $path;
    }

    /**
     * @noRector
     *
     * @codeCoverageIgnore
     */
    protected function createBrowsershot(string $url): Browsershot
    {
        return Browsershot::url($url);
    }
}
