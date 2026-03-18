<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\MatchPlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $match_id
 * @property int $player_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Matches $match
 * @property-read Player $player
 */
final class MatchPlayer extends Model
{
    /** @use HasFactory<MatchPlayerFactory> */
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
}
