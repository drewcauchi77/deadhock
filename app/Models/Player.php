<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $steam_id
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Subscription> $subscriptions
 * @property-read Collection<int, MatchPlayer> $matchPlayers
 * @property-read Collection<int, MatchPost> $matchPosts
 */
final class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Subscription, $this, PlayerSubscription>
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(Subscription::class)
            ->using(PlayerSubscription::class)
            ->withPivot('nice_name')
            ->withTimestamps();
    }

    /**
     * @return HasMany<MatchPlayer, $this>
     */
    public function matchPlayers(): HasMany
    {
        return $this->hasMany(MatchPlayer::class);
    }

    /**
     * @return HasMany<MatchPost, $this>
     */
    public function matchPosts(): HasMany
    {
        return $this->hasMany(MatchPost::class);
    }
}
