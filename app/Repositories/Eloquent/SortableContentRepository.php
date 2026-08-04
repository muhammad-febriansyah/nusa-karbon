<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\SortableContentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Implementasi bersama untuk konten perusahaan berurutan: filter status,
 * penyimpanan urutan, dan nomor urut berikutnya.
 *
 * Subclass menyediakan `query()` beserta `ordered()` dan `activeOrdered()`
 * dengan tipe model konkretnya — koleksi bertipe template tidak terbaca utuh
 * oleh analisis statis, dan mengembalikan tipe yang lebih longgar akan
 * menghilangkan pengecekan di seluruh pemanggilnya.
 *
 * @see docs/09-tasks.md bagian 3.3
 *
 * @template TModel of Model
 *
 * @extends BaseRepository<TModel>
 *
 * @implements SortableContentRepositoryInterface<TModel>
 */
abstract class SortableContentRepository extends BaseRepository implements SortableContentRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $sortable = ['sort_order', 'is_active', 'created_at'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<TModel>
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
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        foreach (array_values($ids) as $position => $id) {
            $this->query()
                ->whereKey($id)
                ->update(['sort_order' => $position]);
        }
    }

    public function nextSortOrder(): int
    {
        return (int) $this->query()->max('sort_order') + 1;
    }
}
