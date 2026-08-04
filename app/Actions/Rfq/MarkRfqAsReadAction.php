<?php

declare(strict_types=1);

namespace App\Actions\Rfq;

use App\Models\RfqRequest;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;

final readonly class MarkRfqAsReadAction
{
    public function __construct(private RfqRequestRepositoryInterface $rfqs) {}

    public function handle(RfqRequest $rfq, bool $read = true): RfqRequest
    {
        return $this->rfqs->update($rfq, ['is_read' => $read]);
    }
}
