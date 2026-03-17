<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureModules();
        $this->configureVite();
    }

    private function configureModules(): void
    {
        Model::shouldBeStrict();
        Model::unguard();
    }

    private function configureVite(): void
    {
        Vite::usePrefetchStrategy('aggressive');
    }
}
