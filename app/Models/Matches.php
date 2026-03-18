<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\MatchesFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Matches extends Model
{
    /** @use HasFactory<MatchesFactory> */
    use HasFactory;
}
