<?php

declare(strict_types=1);

namespace App\Actions\Gallery;

use App\Models\Gallery;
use App\Repositories\Contracts\GalleryRepositoryInterface;

final readonly class DeleteGalleryAction
{
    public function __construct(
        private GalleryRepositoryInterface $galleries,
    ) {}

    /**
     * Album memakai soft delete, jadi itemnya tetap utuh dan album yang
     * dipulihkan kembali lengkap dengan foto dan videonya.
     */
    public function handle(Gallery $gallery): bool
    {
        return $this->galleries->delete($gallery);
    }
}
