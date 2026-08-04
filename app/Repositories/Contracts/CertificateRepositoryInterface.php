<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Certificate>
 */
interface CertificateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Sertifikat aktif berurutan untuk halaman publik.
     *
     * @return Collection<int, Certificate>
     */
    public function activeOrdered(): Collection;

    /**
     * Sertifikat yang disorot di trust bar.
     *
     * @return Collection<int, Certificate>
     */
    public function trustBar(): Collection;

    /**
     * Sertifikat yang kedaluwarsa dalam `$days` hari ke depan, dipakai
     * peringatan dashboard.
     *
     * @return Collection<int, Certificate>
     */
    public function expiringWithin(int $days = Certificate::EXPIRY_WARNING_DAYS): Collection;

    public function nextSortOrder(): int;
}
