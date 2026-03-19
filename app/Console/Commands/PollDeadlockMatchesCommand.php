<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Matches\PollMatchesAction;
use Illuminate\Console\Command;
use Override;

final class PollDeadlockMatchesCommand extends Command
{
    #[Override]
    protected $signature = 'deadlock:poll';

    #[Override]
    protected $description = 'Poll tracked players for new Deadlock matches and post results to Discord';

    public function handle(PollMatchesAction $pollMatchesAction): void
    {
        $this->info('Polling for new matches...');

        $pollMatchesAction->handle();

        $this->info('Polling complete.');
    }
}
