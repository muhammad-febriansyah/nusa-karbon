<?php

declare(strict_types=1);

namespace App\Actions\PackagingOption;

use App\Models\PackagingOption;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class CreatePackagingOptionAction
{
    public function __construct(
        private PackagingOptionRepositoryInterface $options,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): PackagingOption
    {
        $token = isset($data['image_token']) && is_string($data['image_token'])
            ? $data['image_token']
            : null;

        unset($data['image_token']);

        if ($token !== null) {
            $file = $this->media->moveFromToken(
                $token,
                PackagingOption::IMAGE_DIRECTORY,
            );

            $data['image_path'] = $file['path'] ?? null;
        }

        $data['sort_order'] = $this->options->nextSortOrder();

        return $this->options->create($data);
    }
}
