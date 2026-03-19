<?php

declare(strict_types=1);

use DG\BypassFinals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

BypassFinals::enable();

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class);

expect()->extend('toBeOne', fn () => $this->toBe(1));

function something(): void
{
    //
}
