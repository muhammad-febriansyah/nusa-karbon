<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ContainerType;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ContainerType>
 */
final class ContainerTypeRepository extends BaseRepository implements ContainerTypeRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['code', 'name->'.Locale::default()];
    }

    /**
     * @var array<int, string>
     */
    protected array $sortable = [
        'sort_order', 'code', 'max_payload_kg', 'max_cbm', 'is_active', 'created_at',
    ];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<ContainerType>
     */
    protected function query(): Builder
    {
        return ContainerType::query();
    }

    /**
     * @param  Builder<ContainerType>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<ContainerType>
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
     * @return Collection<int, ContainerType>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    public function nextSortOrder(): int
    {
        return (int) ContainerType::query()->max('sort_order') + 1;
    }

    public function codeExists(string $code, ?int $exceptId = null): bool
    {
        return ContainerType::query()
            ->where('code', $code)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}
