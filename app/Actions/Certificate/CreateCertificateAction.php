<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class CreateCertificateAction
{
    public function __construct(
        private CertificateRepositoryInterface $certificates,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Certificate
    {
        $data = $this->moveUploads($data);
        $data['sort_order'] = $this->certificates->nextSortOrder();

        return $this->certificates->create($data);
    }

    /**
     * Logo dan berkas scan diunggah lebih dulu sebagai berkas sementara;
     * di sini keduanya dipindahkan ke disk publik.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function moveUploads(array $data): array
    {
        $logoToken = is_string($data['logo_token'] ?? null) ? $data['logo_token'] : null;
        $fileToken = is_string($data['file_token'] ?? null) ? $data['file_token'] : null;

        unset($data['logo_token'], $data['file_token'], $data['remove_logo'], $data['remove_file']);

        if ($logoToken !== null) {
            $logo = $this->media->moveFromToken($logoToken, Certificate::LOGO_DIRECTORY);
            $data['logo_path'] = $logo['path'] ?? null;
        }

        if ($fileToken !== null) {
            $file = $this->media->moveFromToken($fileToken, Certificate::FILE_DIRECTORY);
            $data['file_path'] = $file['path'] ?? null;
        }

        return $data;
    }
}
