<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Theme>
 */
final class ThemeRepository extends BaseRepository implements ThemeRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = ['name', 'slug'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['name', 'scope', 'created_at'];

    protected string $defaultSortBy = 'name';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'asc';

    /**
     * @return Builder<Theme>
     */
    protected function query(): Builder
    {
        return Theme::query();
    }

    /**
     * @return Collection<int, Theme>
     */
    public function ordered(): Collection
    {
        return $this->query()
            ->orderByDesc('is_preset')
            ->orderBy('name')
            ->get();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Theme::query()
            ->where('slug', $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function deactivateScope(string $scope): void
    {
        $column = $scope === Theme::SCOPE_ADMIN ? 'is_active_admin' : 'is_active_public';

        Theme::query()->where($column, true)->update([$column => false]);
    }
}
