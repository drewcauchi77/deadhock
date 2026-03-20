<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.web')]
final class Privacy extends Component
{
    public function render(): View
    {
        return view('pages.privacy');
    }
}
