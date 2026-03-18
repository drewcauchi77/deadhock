<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $steam_id
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;
}
