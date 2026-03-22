<?php

declare(strict_types=1);

namespace App\Actions\Matches\Screenshot;

use App\Models\Matches;
use App\Models\Subscription;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

trait CreateScreenshotAction
{
    protected function screenshot(
        Matches $match,
        Subscription $subscription,
        string $routeName,
        string $filePrefix,
        int $width,
        int $height,
    ): string {
        $url = route($routeName, [
            'matchId' => $match->match_id,
            'subscription' => $subscription->id,
        ]);

        $directory = storage_path('app/private/screenshots');
        File::ensureDirectoryExists($directory);

        $path = sprintf('%s/%s_%s_%s.png', $directory, $filePrefix, $match->match_id, $subscription->id);

        $this->createBrowsershot($url)
            ->windowSize($width, $height)
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
        $browsershot = Browsershot::url($url);

        /** @var string|null $chromePath */
        $chromePath = config('services.browsershot.chrome_path');

        if ($chromePath !== null) {
            $browsershot->setChromePath($chromePath);
        }

        if (config('services.browsershot.no_sandbox')) {
            $browsershot->noSandbox();
        }

        return $browsershot;
    }
}
