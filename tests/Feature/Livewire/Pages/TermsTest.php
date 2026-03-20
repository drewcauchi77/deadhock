<?php

declare(strict_types=1);

use App\Livewire\Pages\Terms;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Terms::class)
        ->assertSuccessful()
        ->assertSee('Terms of Service');
});
