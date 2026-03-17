<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discord_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('guild_id');
            $table->string('channel_id');
            $table->unique(['guild_id', 'channel_id']);
            $table->timestamps();
        });
    }
};
