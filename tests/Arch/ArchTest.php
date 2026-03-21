<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

test('no debug functions', function (): void {
    expect(['dd', 'dump', 'var_dump', 'ray', 'ddd'])->not->toBeUsed();
});

test('no env calls outside of config', function (): void {
    expect('App')->not->toUse('env');
});

test('strict types', function (): void {
    expect('App')->toUseStrictTypes();
});

test('models extend eloquent', function (): void {
    expect('App\Models')->toExtend(Model::class);
});

test('models use HasFactory', function (): void {
    expect('App\Models')->toUseTrait(HasFactory::class);
});
