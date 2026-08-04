<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<Post>
 */
final class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * Pencarian judul memakai kolom turunan `search_title` yang ber-index,
     * bukan ekspresi JSON yang memindai seluruh tabel.
     *
     * @var array<int, string>
     */
    protected array $searchable = ['search_title'];

    /**
     * @var array<int, string>
     */
    protected array $sortable = ['published_at', 'status', 'view_count', 'created_at'];

    protected string $defaultSortBy = 'created_at';

    /**
     * @var 'asc'|'desc'
     */
    protected string $defaultSortDir = 'desc';

    /**
     * @return Builder<Post>
     */
    protected function query(): Builder
    {
        return Post::query()->with(['category:id,name', 'author:id,name']);
    }

    /**
     * @param  Builder<Post>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Post>
     */
    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $query = parent::applyFilters($query, $filters);

        $status = $filters['status'] ?? null;

        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        $category = $filters['post_category_id'] ?? null;

        if ($category !== null && $category !== '') {
            $query->where('post_category_id', (int) $category);
        }

        $from = $filters['from'] ?? null;

        if (is_string($from) && $from !== '') {
            $query->whereDate('published_at', '>=', $from);
        }

        $to = $filters['to'] ?? null;

        if (is_string($to) && $to !== '') {
            $query->whereDate('published_at', '<=', $to);
        }

        return $query;
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $locale = Locale::default();

        return Post::query()
            ->where("slug->{$locale}", $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    /**
     * @return Collection<int, Post>
     */
    public function dueForPublishing(): Collection
    {
        return Post::query()->dueForPublishing()->get();
    }

    /**
     * @return Collection<int, Post>
     */
    public function latestPublished(int $limit): Collection
    {
        return Post::query()
            ->published()
            ->with(['category:id,name'])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function countPublished(): int
    {
        return Post::query()->published()->count();
    }

    /**
     * @return Collection<int, Post>
     */
    public function publishedForPublic(?int $categoryId = null): Collection
    {
        return Post::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->when(
                $categoryId !== null,
                fn (Builder $query): Builder => $query->where('post_category_id', $categoryId),
            )
            ->orderByDesc('published_at')
            ->get();
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function publishedPaginated(
        ?int $categoryId,
        int $perPage,
    ): LengthAwarePaginator {
        return Post::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->when(
                $categoryId !== null,
                fn (Builder $query): Builder => $query->where('post_category_id', $categoryId),
            )
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug, string $locale): ?Post
    {
        return Post::query()
            ->published()
            ->where("slug->{$locale}", $slug)
            ->first();
    }
}
