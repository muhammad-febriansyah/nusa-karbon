<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Repositories\Contracts\SortableContentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Soft delete, jadi berkasnya sengaja dibiarkan: konten yang dipulihkan harus
 * kembali lengkap dengan gambarnya.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final readonly class DeleteSortableContentAction
{
    /**
     * @template TModel of Model
     *
     * @param  SortableContentRepositoryInterface<TModel>  $repository
     * @param  TModel  $model
     */
    public function handle(SortableContentRepositoryInterface $repository, Model $model): bool
    {
        return $repository->delete($model);
    }
}
