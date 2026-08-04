<?php

declare(strict_types=1);

namespace App\Actions\Representative;

use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;

final readonly class DeleteRepresentativeAction
{
    public function __construct(
        private RepresentativeRepositoryInterface $representatives,
    ) {}

    /**
     * Soft delete, sehingga jejak perwakilan lama tetap bisa ditelusuri saat
     * ada sengketa atau laporan penipuan.
     */
    public function handle(Representative $representative): bool
    {
        return $this->representatives->delete($representative);
    }
}
