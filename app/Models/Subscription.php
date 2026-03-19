<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $guild_id
 * @property string $channel_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Player> $players
 * @property-read Collection<int, MatchPost> $matchPosts
 */
final class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return BelongsToMany<Player, $this, PlayerSubscription>
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class)
            ->using(PlayerSubscription::class)
            ->withPivot('nice_name')
            ->withTimestamps();
    }

    /**
     * @return HasMany<MatchPost, $this>
     */
    public function matchPosts(): HasMany
    {
        return $this->hasMany(MatchPost::class);
    }
}
