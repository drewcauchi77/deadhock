<?php

declare(strict_types=1);

namespace App\Actions\Matches;

use App\Actions\Matches\Message\CreateMatchMessageAction;
use App\Actions\Matches\Message\CreateMvpMessageAction;
use App\Actions\Matches\Screenshot\ScreenshotMatchAction;
use App\Actions\Matches\Screenshot\ScreenshotMvpAction;
use App\Models\Matches;
use App\Models\MatchPost;
use App\Models\Subscription;
use App\Services\Discord\DiscordBotService;
use Illuminate\Database\UniqueConstraintViolationException;

final readonly class SendToSubscriptionsAction
{
    public function __construct(
        private ScreenshotMatchAction $screenshotMatchAction,
        private ScreenshotMvpAction $screenshotMvpAction,
        private CreateMatchMessageAction $createMatchMessageAction,
        private CreateMvpMessageAction $createMvpMessageAction,
        private DiscordBotService $discordBotService,
    ) {}

    public function handle(Matches $match): void
    {
        $subscriptions = Subscription::query()
            ->whereHas('players', function ($query) use ($match): void {
                $query->whereIn('players.id', $match->matchPlayers()->pluck('player_id'));
            })
            ->with('players')
            ->get();

        foreach ($subscriptions as $subscription) {
            if (MatchPost::query()->where('match_id', $match->id)->where('subscription_id', $subscription->id)->exists()) {
                continue;
            }

            $imagePath = $this->screenshotMatchAction->handle($match, $subscription);

            $message = $this->createMatchMessageAction->handle($match, $subscription);

            $this->discordBotService->postMatchToChannel(
                $subscription->channel_id,
                $imagePath,
                $message,
            );

            $mvpMessage = $this->createMvpMessageAction->handle($match, $subscription);

            if ($mvpMessage !== '') {
                $mvpPath = $this->screenshotMvpAction->handle($match, $subscription);

                $this->discordBotService->postMatchToChannel(
                    $subscription->channel_id,
                    $mvpPath,
                    $mvpMessage,
                );
            }

            try {
                MatchPost::query()->create([
                    'match_id' => $match->id,
                    'player_id' => $subscription->players->first()?->id,
                    'subscription_id' => $subscription->id,
                ]);
            } catch (UniqueConstraintViolationException) {
                //
            }
        }
    }
}
