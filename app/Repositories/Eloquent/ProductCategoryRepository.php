<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ProductCategory>
 */
final class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
{
    /**
     * Pencarian menyasar nama pada locale bawaan, satu-satunya yang wajib
     * diisi. Ditetapkan di konstruktor agar ikut `config/locales.php`.
     *
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['name->'.Locale::default()];
    }

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['sort_order', 'created_at', 'is_active'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<ProductCategory>
     */
    protected function query(): Builder
    {
        return ProductCategory::query();
    }

    /**
     * @param  Builder<ProductCategory>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<ProductCategory>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['is_active'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('is_active', filter_var($status, FILTER_VALIDATE_BOOLEAN));
        }

        return $query;
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            ProductCategory::query()
                ->whereKey($id)
                ->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(): int
    {
        return (int) ProductCategory::query()->max('sort_order') + 1;
    }

    public function slugExists(string $locale, string $slug, ?int $exceptId = null): bool
    {
        return ProductCategory::query()
            ->where("slug->{$locale}", $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}
