<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PlayerSubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $subscription_id
 * @property int $player_id
 * @property string $nice_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class PlayerSubscription extends Pivot
{
    /** @use HasFactory<PlayerSubscriptionFactory> */
    use HasFactory;
}
