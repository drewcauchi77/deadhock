<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Subscription;
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

        Subscription::factory(5)->create();

        Player::factory(16)->create();
        Player::factory(4)->unchecked()->create();
    }
}
