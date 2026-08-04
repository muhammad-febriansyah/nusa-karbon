<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;

final readonly class DeleteCertificateAction
{
    public function __construct(
        private CertificateRepositoryInterface $certificates,
    ) {}

    /**
     * Soft delete, jadi logo dan berkas scan sengaja dibiarkan: sertifikat
     * yang dipulihkan harus kembali lengkap dengan berkasnya.
     */
    public function handle(Certificate $certificate): bool
    {
        return $this->certificates->delete($certificate);
    }
}
