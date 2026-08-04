<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Kontrak bersama untuk konten perusahaan yang polanya identik: daftar pendek,
 * diurutkan manual lewat drag-and-drop, dan punya penanda aktif.
 *
 * @see docs/09-tasks.md bagian 3.3
 *
 * @template TModel of Model
 *
 * @extends BaseRepositoryInterface<TModel>
 */
interface SortableContentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return Collection<int, TModel>
     */
    public function ordered(): Collection;

    /**
     * @return Collection<int, TModel>
     */
    public function activeOrdered(): Collection;

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void;

    public function nextSortOrder(): int;
}
