<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\PostCategory;
use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<PostCategory>
 */
final class PostCategoryRepository extends SortableContentRepository implements PostCategoryRepositoryInterface
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
     * @return Builder<PostCategory>
     */
    protected function query(): Builder
    {
        return PostCategory::query();
    }

    /**
     * @return Collection<int, PostCategory>
     */
    public function ordered(): Collection
    {
        return $this->query()->withCount('posts')->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, PostCategory>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $locale = Locale::default();

        return PostCategory::query()
            ->where("slug->{$locale}", $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function countPosts(int $postCategoryId): int
    {
        return PostCategory::query()
            ->whereKey($postCategoryId)
            ->withCount('posts')
            ->value('posts_count') ?? 0;
    }
}
