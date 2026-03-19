<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained();
            $table->foreignUuid('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('discord_message_id', 32)->nullable();
            $table->timestamp('posted_at')->useCurrent();
            $table->unique(['match_id', 'subscription_id']);
            $table->timestamps();
        });
    }
};
