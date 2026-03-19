<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\MatchPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $match_id
 * @property int $player_id
 * @property string $subscription_id
 * @property string|null $discord_message_id
 * @property Carbon $posted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Matches $match
 * @property-read Player $player
 * @property-read Subscription $subscription
 */
final class MatchPost extends Model
{
    /** @use HasFactory<MatchPostFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Matches, $this>
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(Matches::class);
    }

    /**
     * @return BelongsTo<Player, $this>
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
        ];
    }
}
