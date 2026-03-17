<?php

declare(strict_types=1);

use App\Models\User;

it('includes expected fields in toArray', function (): void {
    $user = User::factory()->make();

    expect($user->toArray())->toHaveKeys(['name', 'email']);
});

it('excludes hidden fields from toArray', function (): void {
    $user = User::factory()->make();

    expect($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});
