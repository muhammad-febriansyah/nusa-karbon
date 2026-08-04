<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Services\MediaUploadService;

final readonly class UpdateCertificateAction
{
    public function __construct(
        private CertificateRepositoryInterface $certificates,
        private MediaUploadService $media,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Certificate $certificate, array $data): Certificate
    {
        $logoToken = is_string($data['logo_token'] ?? null) ? $data['logo_token'] : null;
        $fileToken = is_string($data['file_token'] ?? null) ? $data['file_token'] : null;
        $removeLogo = (bool) ($data['remove_logo'] ?? false);
        $removeFile = (bool) ($data['remove_file'] ?? false);

        unset($data['logo_token'], $data['file_token'], $data['remove_logo'], $data['remove_file']);

        $previousLogo = $certificate->logo_path;
        $previousFile = $certificate->file_path;

        if ($logoToken !== null) {
            $logo = $this->media->moveFromToken($logoToken, Certificate::LOGO_DIRECTORY);

            if ($logo !== null) {
                $data['logo_path'] = $logo['path'];
            }
        } elseif ($removeLogo) {
            $data['logo_path'] = null;
        }

        if ($fileToken !== null) {
            $file = $this->media->moveFromToken($fileToken, Certificate::FILE_DIRECTORY);

            if ($file !== null) {
                $data['file_path'] = $file['path'];
            }
        } elseif ($removeFile) {
            $data['file_path'] = null;
        }

        $certificate = $this->certificates->update($certificate, $data);

        // Berkas lama baru dihapus setelah baris menunjuk berkas baru.
        if ($previousLogo !== null && $previousLogo !== $certificate->logo_path) {
            $this->media->delete($previousLogo);
        }

        if ($previousFile !== null && $previousFile !== $certificate->file_path) {
            $this->media->delete($previousFile);
        }

        return $certificate;
    }
}
