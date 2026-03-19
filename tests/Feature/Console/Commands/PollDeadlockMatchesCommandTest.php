<?php

declare(strict_types=1);

use App\Actions\Matches\PollMatchesAction;

use function Pest\Laravel\mock;

it('calls poll matches action and outputs status', function (): void {
    mock(PollMatchesAction::class)
        ->shouldReceive('handle')->once();

    $this->artisan('deadlock:poll')
        ->expectsOutput('Polling for new matches...')
        ->expectsOutput('Polling complete.')
        ->assertSuccessful();
});
