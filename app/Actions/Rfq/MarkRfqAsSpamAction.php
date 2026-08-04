<?php

declare(strict_types=1);

namespace App\Actions\Rfq;

use App\Enums\RfqStatus;
use App\Models\RfqRequest;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;

/**
 * RFQ spam tidak dihapus: nomornya tetap tercatat supaya urutan referensi
 * tidak berlubang dan pola pengirimnya bisa ditelusuri.
 */
final readonly class MarkRfqAsSpamAction
{
    public function __construct(private RfqRequestRepositoryInterface $rfqs) {}

    public function handle(RfqRequest $rfq): RfqRequest
    {
        return $this->rfqs->update($rfq, [
            'status' => RfqStatus::Spam->value,
            'is_read' => true,
        ]);
    }
}
