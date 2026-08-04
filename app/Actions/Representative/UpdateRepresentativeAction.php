<?php

declare(strict_types=1);

namespace App\Actions\Representative;

use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class UpdateRepresentativeAction
{
    public function __construct(
        private RepresentativeRepositoryInterface $representatives,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Representative $representative, array $data): Representative
    {
        $token = is_string($data['photo_token'] ?? null) ? $data['photo_token'] : null;
        $removePhoto = (bool) ($data['remove_photo'] ?? false);

        unset($data['photo_token'], $data['remove_photo']);

        $previousPath = $representative->photo_path;

        if ($token !== null) {
            $photo = $this->media->moveFromToken($token, Representative::PHOTO_DIRECTORY);

            if ($photo !== null) {
                $data['photo_path'] = $photo['path'];
            }
        } elseif ($removePhoto) {
            $data['photo_path'] = null;
        }

        $representative = $this->representatives->update($representative, $data);

        // Foto lama baru dihapus setelah baris menunjuk foto baru.
        if ($previousPath !== null && $previousPath !== $representative->photo_path) {
            $this->media->delete($previousPath);
        }

        return $representative;
    }
}
