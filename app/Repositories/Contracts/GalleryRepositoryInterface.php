<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Gallery;

/**
 * @extends SortableContentRepositoryInterface<Gallery>
 */
interface GalleryRepositoryInterface extends SortableContentRepositoryInterface
{
    public function slugExists(string $slug, ?int $exceptId = null): bool;

    /**
     * Album beserta itemnya, dipakai halaman kelola item.
     */
    public function withItems(Gallery $gallery): Gallery;

    /**
     * Nomor urut item berikutnya di dalam album.
     */
    public function nextItemSortOrder(int $galleryId): int;
}
