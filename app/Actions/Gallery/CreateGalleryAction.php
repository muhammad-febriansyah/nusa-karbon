<?php

declare(strict_types=1);

namespace App\Actions\Gallery;

use App\Models\Gallery;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class CreateGalleryAction
{
    public function __construct(
        private GalleryRepositoryInterface $galleries,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Gallery
    {
        $token = is_string($data['cover_token'] ?? null) ? $data['cover_token'] : null;

        unset($data['cover_token'], $data['remove_cover']);

        if ($token !== null) {
            $cover = $this->media->moveFromToken($token, Gallery::COVER_DIRECTORY);
            $data['cover_path'] = $cover['path'] ?? null;
        }

        $data['sort_order'] = $this->galleries->nextSortOrder();

        return $this->galleries->create($data);
    }
}
