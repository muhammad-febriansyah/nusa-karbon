<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PackagingOption;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<PackagingOption>
 */
final class PackagingOptionRepository extends BaseRepository implements PackagingOptionRepositoryInterface
{
    /**
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
    protected array $sortable = ['sort_order', 'net_weight_kg', 'is_active', 'created_at'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<PackagingOption>
     */
    protected function query(): Builder
    {
        return PackagingOption::query();
    }

    /**
     * @param  Builder<PackagingOption>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<PackagingOption>
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
     * @return Collection<int, PackagingOption>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, PackagingOption>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            PackagingOption::query()
                ->whereKey($id)
                ->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(): int
    {
        return (int) PackagingOption::query()->max('sort_order') + 1;
    }

    public function countProducts(int $packagingOptionId): int
    {
        return PackagingOption::query()
            ->whereKey($packagingOptionId)
            ->withCount('products')
            ->value('products_count') ?? 0;
    }
}
