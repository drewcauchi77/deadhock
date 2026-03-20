<?php

declare(strict_types=1);

use App\Http\Controllers\DiscordInteractionController;
use App\Http\Middleware\VerifyDiscordSignature;
use App\Livewire\Pages\Home;
use App\Livewire\Pages\Matches\Show;
use App\Livewire\Pages\Privacy;
use App\Livewire\Pages\Terms;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');
Route::livewire('/terms', Terms::class)->name('terms');
Route::livewire('/privacy', Privacy::class)->name('privacy');

Route::post('/discord/interactions', [DiscordInteractionController::class, 'invoke'])
    ->name('discord.interactions')
    ->middleware(VerifyDiscordSignature::class);

Route::livewire('/matches/{matchId}/{subscription}', Show::class)
    ->name('matches.show');
