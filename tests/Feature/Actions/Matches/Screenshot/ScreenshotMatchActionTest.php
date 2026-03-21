<?php

declare(strict_types=1);

use App\Actions\Matches\Screenshot\ScreenshotMatchAction;
use App\Models\Matches;
use App\Models\Subscription;
use Spatie\Browsershot\Browsershot;

it('calls browsershot with correct url, dimensions, and path', function (): void {
    $match = Matches::factory()->create(['match_id' => '55555']);
    $subscription = Subscription::factory()->create();

    $expectedUrl = route('matches.show', [
        'matchId' => '55555',
        'subscription' => $subscription->id,
    ]);

    $expectedPath = storage_path(sprintf('app/private/screenshots/match_55555_%s.png', $subscription->id));

    $browsershotMock = Mockery::mock(Browsershot::class);
    $browsershotMock->shouldReceive('windowSize')->once()->with(1248, 1106)->andReturnSelf();
    $browsershotMock->shouldReceive('save')->once()->with($expectedPath);

    $action = Mockery::mock(ScreenshotMatchAction::class)->makePartial()->shouldAllowMockingProtectedMethods();
    $action->shouldReceive('createBrowsershot')->once()->with($expectedUrl)->andReturn($browsershotMock);

    $result = $action->handle($match, $subscription);

    expect($result)->toBe($expectedPath);
});
