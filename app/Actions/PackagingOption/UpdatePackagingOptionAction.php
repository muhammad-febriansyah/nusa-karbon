<?php

declare(strict_types=1);

namespace App\Actions\PackagingOption;

use App\Models\PackagingOption;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class UpdatePackagingOptionAction
{
    public function __construct(
        private PackagingOptionRepositoryInterface $options,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(PackagingOption $option, array $data): PackagingOption
    {
        $token = isset($data['image_token']) && is_string($data['image_token'])
            ? $data['image_token']
            : null;
        $removeImage = (bool) ($data['remove_image'] ?? false);

        unset($data['image_token'], $data['remove_image']);

        $previousPath = $option->image_path;

        if ($token !== null) {
            $file = $this->media->moveFromToken(
                $token,
                PackagingOption::IMAGE_DIRECTORY,
            );

            if ($file !== null) {
                $data['image_path'] = $file['path'];
            }
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        $option = $this->options->update($option, $data);

        // Berkas lama baru dihapus setelah baris menunjuk berkas baru.
        if ($previousPath !== null && $previousPath !== $option->image_path) {
            $this->media->delete($previousPath);
        }

        return $option;
    }
}
