<?php

declare(strict_types=1);

namespace App\Actions\Rfq;

use App\Enums\RfqStatus;
use App\Models\RfqRequest;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;

final readonly class UpdateRfqStatusAction
{
    public function __construct(private RfqRequestRepositoryInterface $rfqs) {}

    public function handle(RfqRequest $rfq, RfqStatus $status): RfqRequest
    {
        // Mengubah status berarti RFQ sudah dilihat admin.
        return $this->rfqs->update($rfq, [
            'status' => $status->value,
            'is_read' => true,
        ]);
    }
}
