<?php

declare(strict_types=1);

use App\Actions\Matches\Screenshot\ScreenshotMvpAction;
use App\Models\Matches;
use App\Models\Subscription;
use Spatie\Browsershot\Browsershot;

it('calls browsershot with correct url, dimensions, and path', function (): void {
    $match = Matches::factory()->create(['match_id' => '77777']);
    $subscription = Subscription::factory()->create();

    $expectedUrl = route('matches.mvp', [
        'matchId' => '77777',
        'subscription' => $subscription->id,
    ]);

    $expectedPath = storage_path(sprintf('app/private/screenshots/mvp_77777_%s.png', $subscription->id));

    $browsershotMock = Mockery::mock(Browsershot::class);
    $browsershotMock->shouldReceive('windowSize')->once()->with(800, 600)->andReturnSelf();
    $browsershotMock->shouldReceive('save')->once()->with($expectedPath);

    $action = Mockery::mock(ScreenshotMvpAction::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('createBrowsershot')->once()->with($expectedUrl)->andReturn($browsershotMock);

    $result = $action->handle($match, $subscription);

    expect($result)->toBe($expectedPath);
});
