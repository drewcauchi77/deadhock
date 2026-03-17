<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\DiscordSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $guild_id
 * @property string $channel_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class DiscordSubscription extends Model
{
    /** @use HasFactory<DiscordSubscriptionFactory> */
    use HasFactory;
}
