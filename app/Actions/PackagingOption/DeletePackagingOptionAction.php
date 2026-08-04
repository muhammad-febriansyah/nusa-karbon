<?php

declare(strict_types=1);

namespace App\Actions\PackagingOption;

use App\Models\PackagingOption;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;

final readonly class DeletePackagingOptionAction
{
    public function __construct(
        private PackagingOptionRepositoryInterface $options,
    ) {}

    /**
     * Soft delete, jadi gambarnya sengaja dibiarkan: opsi yang dipulihkan
     * harus kembali lengkap dengan gambarnya.
     */
    public function handle(PackagingOption $option): bool
    {
        return $this->options->delete($option);
    }
}
