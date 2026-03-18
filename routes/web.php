<?php

declare(strict_types=1);

use App\Http\Controllers\DiscordInteractionController;
use App\Http\Middleware\VerifyDiscordSignature;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): Factory|View => view('welcome'));

Route::post('/discord/interactions', [DiscordInteractionController::class, 'invoke'])
    ->name('discord.interactions')
    ->middleware(VerifyDiscordSignature::class);

Route::livewire('/matches/{matchId}', 'pages.matches.show')
    ->name('matches.show');
