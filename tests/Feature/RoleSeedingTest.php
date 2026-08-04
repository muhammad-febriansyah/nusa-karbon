<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

test('seeder hanya membuat satu role admin', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::query()->pluck('name')->all())->toBe(['admin']);
});

test('seeder role dapat dijalankan berulang tanpa menduplikasi', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::query()->where('name', 'admin')->count())->toBe(1);
});

test('pengguna dapat diberi role admin', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('admin');

    expect($user->hasRole('admin'))->toBeTrue();
});
