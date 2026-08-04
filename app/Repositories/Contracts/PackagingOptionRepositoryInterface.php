<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PackagingOption;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<PackagingOption>
 */
interface PackagingOptionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Seluruh opsi berurutan, dipakai mode atur urutan.
     *
     * @return Collection<int, PackagingOption>
     */
    public function ordered(): Collection;

    /**
     * Opsi aktif berurutan, dipakai multi-select di form produk.
     *
     * @return Collection<int, PackagingOption>
     */
    public function activeOrdered(): Collection;

    /**
     * @param  array<int, int>  $ids  Urutan baru, indeks larik menjadi sort_order.
     */
    public function reorder(array $ids): void;

    public function nextSortOrder(): int;

    /**
     * Jumlah produk yang masih memakai opsi kemasan ini.
     */
    public function countProducts(int $packagingOptionId): int;
}
