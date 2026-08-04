<?php

declare(strict_types=1);

namespace App\Actions\Rfq;

use App\Models\RfqRequest;
use App\Notifications\NewRfqReceived;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Menyimpan RFQ baru dari form publik beserta nomor referensinya, lalu
 * mengirim notifikasi ke tim sales.
 *
 * @see docs/09-tasks.md bagian 4.1
 */
final readonly class CreateRfqRequestAction
{
    public function __construct(private RfqRequestRepositoryInterface $rfqs) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $notifyEmails
     */
    public function handle(array $data, array $notifyEmails = []): RfqRequest
    {
        $rfq = DB::transaction(function () use ($data): RfqRequest {
            $data['reference_no'] = $this->rfqs->nextReferenceNo();

            return $this->rfqs->create($data);
        });

        $this->notify($rfq, $notifyEmails);

        return $rfq;
    }

    /**
     * Kegagalan notifikasi tidak boleh menggagalkan penyimpanan lead — RFQ
     * yang sudah tersimpan jauh lebih berharga daripada satu email.
     *
     * @param  list<string>  $emails
     */
    private function notify(RfqRequest $rfq, array $emails): void
    {
        if ($emails === []) {
            return;
        }

        try {
            Notification::route('mail', $emails)->notify(new NewRfqReceived($rfq));
        } catch (\Throwable $exception) {
            Log::error('Notifikasi RFQ baru gagal dikirim.', [
                'reference_no' => $rfq->reference_no,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
