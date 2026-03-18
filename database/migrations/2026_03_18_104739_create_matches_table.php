<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table): void {
            $table->id();
            $table->string('match_id')->unique();
            $table->timestamp('match_started_at')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->timestamps();
        });
    }
};
