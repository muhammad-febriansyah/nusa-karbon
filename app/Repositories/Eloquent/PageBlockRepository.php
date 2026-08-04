<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PageBlock;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<PageBlock>
 */
final class PageBlockRepository extends BaseRepository implements PageBlockRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $sortable = ['page_key', 'sort_order', 'is_active'];

    protected string $defaultSortBy = 'sort_order';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<PageBlock>
     */
    protected function query(): Builder
    {
        return PageBlock::query();
    }

    /**
     * @return Collection<int, PageBlock>
     */
    public function ordered(): Collection
    {
        return $this->query()
            ->orderBy('page_key')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, PageBlock>
     */
    public function activeForPage(string $pageKey): Collection
    {
        return $this->query()->active()->forPageKey($pageKey)->get();
    }
}
