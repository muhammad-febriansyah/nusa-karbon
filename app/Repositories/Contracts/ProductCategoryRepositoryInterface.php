<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<ProductCategory>
 */
interface ProductCategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, ProductCategory>
     */
    public function ordered(): Collection;

    /**
     * @param  array<int, int>  $ids  Urutan baru, indeks larik menjadi sort_order.
     */
    public function reorder(array $ids): void;

    public function nextSortOrder(): int;

    /**
     * Slug wajib unik per locale.
     */
    public function slugExists(string $locale, string $slug, ?int $exceptId = null): bool;
}
