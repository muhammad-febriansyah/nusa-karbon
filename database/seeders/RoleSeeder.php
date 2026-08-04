<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Proyek ini hanya memakai satu role: `admin`. `spatie/laravel-permission`
 * tetap dipasang agar penambahan role di masa depan tidak perlu refactor.
 * Jangan membuat role kedua kecuali diminta.
 *
 * @see docs/CLAUDE.md bagian "Pengguna & Role"
 */
final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::query()->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }
}
