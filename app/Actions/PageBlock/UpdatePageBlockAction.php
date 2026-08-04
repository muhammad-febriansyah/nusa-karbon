<?php

declare(strict_types=1);

namespace App\Actions\PageBlock;

use App\Models\PageBlock;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class UpdatePageBlockAction
{
    public function __construct(
        private PageBlockRepositoryInterface $blocks,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(PageBlock $block, array $data): PageBlock
    {
        $token = is_string($data['image_path_token'] ?? null)
            ? $data['image_path_token']
            : null;
        $remove = (bool) ($data['remove_image_path'] ?? false);

        unset($data['image_path_token'], $data['remove_image_path']);

        $previousPath = $block->image_path;

        if ($token !== null) {
            $image = $this->media->moveFromToken($token, PageBlock::IMAGE_DIRECTORY);

            if ($image !== null) {
                $data['image_path'] = $image['path'];
            }
        } elseif ($remove) {
            $data['image_path'] = null;
        }

        $block = $this->blocks->update($block, $data);

        // Gambar lama baru dihapus setelah baris menunjuk gambar baru.
        if ($previousPath !== null && $previousPath !== $block->image_path) {
            $this->media->delete($previousPath);
        }

        return $block;
    }
}
