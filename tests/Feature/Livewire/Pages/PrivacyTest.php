<?php

declare(strict_types=1);

use App\Livewire\Pages\Privacy;
use Livewire\Livewire;

it('renders successfully', function (): void {
    Livewire::test(Privacy::class)
        ->assertSuccessful()
        ->assertSee('Privacy Policy');
});
