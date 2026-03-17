<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DiscordSubscription;
use App\Models\TrackedPlayer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        DiscordSubscription::factory(5)->create();

        TrackedPlayer::factory(16)->create();
        TrackedPlayer::factory(4)->unchecked()->create();
    }
}
