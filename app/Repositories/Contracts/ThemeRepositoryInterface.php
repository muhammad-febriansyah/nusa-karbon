<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Theme;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Theme>
 */
interface ThemeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, Theme>
     */
    public function ordered(): Collection;

    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * Menonaktifkan seluruh tema pada satu scope; dipakai sebelum
     * mengaktifkan tema baru dalam transaksi yang sama.
     */
    public function deactivateScope(string $scope): void;
}
