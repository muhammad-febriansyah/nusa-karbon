<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\PageBlock;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<PageBlock>
 */
interface PageBlockRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Seluruh blok berurutan per halaman.
     *
     * @return Collection<int, PageBlock>
     */
    public function ordered(): Collection;

    /**
     * Blok aktif satu halaman, dipakai halaman publik.
     *
     * @return Collection<int, PageBlock>
     */
    public function activeForPage(string $pageKey): Collection;
}
