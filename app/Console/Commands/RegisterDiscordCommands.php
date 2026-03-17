<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Discord\DiscordBotService;
use Illuminate\Console\Command;
use Override;

final class RegisterDiscordCommands extends Command
{
    #[Override]
    protected $signature = 'discord:register-commands {--guild= : Register to a specific guild for instant propagation (dev only)}';

    #[Override]
    protected $description = 'Register the /deadhock slash command with Discord';

    public function handle(DiscordBotService $discordBotService): void
    {
        $guildId = $this->option('guild');

        if (is_string($guildId)) {
            $discordBotService->registerGuildCommand($guildId);
            $this->info(sprintf('Slash command registered to guild %s (instant).', $guildId));

            return;
        }

        $discordBotService->registerGlobalCommand();
        $this->info('Slash command registered globally (propagates within 1 hour).');
    }
}
