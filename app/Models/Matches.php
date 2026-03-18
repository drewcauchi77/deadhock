<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\MatchesFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $match_id
 * @property Carbon|null $match_started_at
 * @property Carbon $fetched_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, MatchPlayer> $matchPlayers
 * @property-read Collection<int, MatchPost> $matchPosts
 */
final class Matches extends Model
{
    /** @use HasFactory<MatchesFactory> */
    use HasFactory;

    public function getForeignKey(): string
    {
        return 'match_id';
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'match_started_at' => 'datetime',
            'fetched_at' => 'datetime',
        ];
    }
}
