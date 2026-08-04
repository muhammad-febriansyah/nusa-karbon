<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * @extends BaseRepositoryInterface<User>
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Pilihan pengguna untuk filter dan select.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function options(): array;

    /**
     * Jumlah admin yang masih aktif. Dipakai proteksi "admin aktif terakhir".
     */
    public function countActiveAdmins(): int;
}
