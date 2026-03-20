<?php

declare(strict_types=1);

use App\Livewire\Pages\Home;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Home::class)
        ->assertSuccessful()
        ->assertSee('Deadlock');
});
