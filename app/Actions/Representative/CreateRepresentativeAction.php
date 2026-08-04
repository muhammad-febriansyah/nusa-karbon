<?php

declare(strict_types=1);

namespace App\Actions\Representative;

use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class CreateRepresentativeAction
{
    public function __construct(
        private RepresentativeRepositoryInterface $representatives,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Representative
    {
        $token = is_string($data['photo_token'] ?? null) ? $data['photo_token'] : null;

        unset($data['photo_token'], $data['remove_photo']);

        if ($token !== null) {
            $photo = $this->media->moveFromToken($token, Representative::PHOTO_DIRECTORY);
            $data['photo_path'] = $photo['path'] ?? null;
        }

        $data['sort_order'] = $this->representatives->nextSortOrder();

        return $this->representatives->create($data);
    }
}
