<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Repository hanya berisi akses data: query, filter, pagination, eager loading.
 * Logika bisnis dan transaksi tinggal di Service atau Action.
 *
 * @see docs/06-architecture.md bagian 2
 *
 * @template TModel of Model
 *
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Kolom yang ikut dicari oleh filter `search`.
     *
     * @var array<int, string>
     */
    protected array $searchable = [];

    /**
     * Daftar putih kolom yang boleh dipakai untuk sorting.
     *
     * @var array<int, string>
     */
    protected array $sortable = ['id'];

    protected string $defaultSortBy = 'id';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'desc';

    /**
     * Setiap repository menyediakan query dasarnya sendiri, misalnya
     * `return Product::query()->with(['category:id,name']);`
     *
     * @return Builder<TModel>
     */
    abstract protected function query(): Builder;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->applySorting($this->applyFilters($this->query(), $filters), $filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return TModel
     */
    public function findOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     * @return TModel
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->refresh();
    }

    /**
     * @param  TModel  $model
     */
    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Subclass menimpa metode ini untuk menambah filter khusus modulnya.
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $search = $filters['search'] ?? null;

        if (is_string($search) && $search !== '' && $this->searchable !== []) {
            $query->where(function (Builder $query) use ($search): void {
                foreach ($this->searchable as $index => $column) {
                    $index === 0
                        ? $query->where($column, 'like', "%{$search}%")
                        : $query->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        return $query;
    }

    /**
     * Kolom dan arah sort divalidasi terhadap daftar putih agar tidak bisa
     * diinjeksi lewat query string.
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
     */
    protected function applySorting(Builder $query, array $filters): Builder
    {
        $sortBy = $filters['sort_by'] ?? null;
        $sortDir = $filters['sort_dir'] ?? null;

        $column = is_string($sortBy) && in_array($sortBy, $this->sortable, true)
            ? $sortBy
            : $this->defaultSortBy;

        $direction = match (is_string($sortDir) ? strtolower($sortDir) : null) {
            'asc' => 'asc',
            'desc' => 'desc',
            default => $this->defaultSortDir,
        };

        return $query->orderBy($column, $direction);
    }
}
