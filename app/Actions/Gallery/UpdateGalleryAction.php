<?php

declare(strict_types=1);

namespace App\Actions\Gallery;

use App\Models\Gallery;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class UpdateGalleryAction
{
    public function __construct(
        private GalleryRepositoryInterface $galleries,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Gallery $gallery, array $data): Gallery
    {
        $token = is_string($data['cover_token'] ?? null) ? $data['cover_token'] : null;
        $remove = (bool) ($data['remove_cover'] ?? false);

        unset($data['cover_token'], $data['remove_cover']);

        $previousPath = $gallery->cover_path;

        if ($token !== null) {
            $cover = $this->media->moveFromToken($token, Gallery::COVER_DIRECTORY);

            if ($cover !== null) {
                $data['cover_path'] = $cover['path'];
            }
        } elseif ($remove) {
            $data['cover_path'] = null;
        }

        $gallery = $this->galleries->update($gallery, $data);

        if ($previousPath !== null && $previousPath !== $gallery->cover_path) {
            $this->media->delete($previousPath);
        }

        return $gallery;
    }
}
