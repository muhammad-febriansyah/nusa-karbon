<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<Gallery>
 */
final class GalleryRepository extends SortableContentRepository implements GalleryRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['title->'.Locale::default()];
    }

    /**
     * @return Builder<Gallery>
     */
    protected function query(): Builder
    {
        return Gallery::query();
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function ordered(): Collection
    {
        return $this->query()->withCount('items')->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $locale = Locale::default();

        return Gallery::query()
            ->where("slug->{$locale}", $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->whereKeyNot($exceptId))
            ->exists();
    }

    public function withItems(Gallery $gallery): Gallery
    {
        return $gallery->load('items');
    }

    public function nextItemSortOrder(int $galleryId): int
    {
        return (int) GalleryItem::query()
            ->where('gallery_id', $galleryId)
            ->max('sort_order') + 1;
    }
}
