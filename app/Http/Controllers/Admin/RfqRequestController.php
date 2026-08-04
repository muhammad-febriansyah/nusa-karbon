<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Rfq\AddRfqNoteAction;
use App\Actions\Rfq\MarkRfqAsReadAction;
use App\Actions\Rfq\MarkRfqAsSpamAction;
use App\Actions\Rfq\UpdateRfqStatusAction;
use App\Enums\RfqStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\RfqRequestResource;
use App\Models\RfqRequest;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RfqRequestController extends Controller
{
    /**
     * Kolom ekspor CSV beserta judulnya.
     */
    private const EXPORT_COLUMNS = [
        'reference_no' => 'Nomor Referensi',
        'created_at' => 'Tanggal Masuk',
        'company_name' => 'Perusahaan',
        'contact_name' => 'Nama Kontak',
        'email' => 'Email',
        'phone' => 'Telepon',
        'whatsapp' => 'WhatsApp',
        'country_code' => 'Negara',
        'incoterms' => 'Incoterms',
        'port_of_destination' => 'Pelabuhan Tujuan',
        'monthly_tonnage' => 'Perkiraan Volume',
        'status' => 'Status',
    ];

    public function __construct(
        private readonly RfqRequestRepositoryInterface $rfqs,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'status', 'country_code', 'product_id', 'unread',
            'from', 'to', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/rfqs/index', [
            'rfqs' => RfqRequestResource::collection(
                $this->rfqs->paginate($filters, $request->integer('per_page', 15)),
            ),
            'statuses' => RfqStatus::options(),
            'unreadCount' => $this->rfqs->countUnread(),
            'filters' => $filters,
        ]);
    }

    /**
     * Membuka detail sekaligus menandai RFQ sudah dibaca.
     */
    public function show(RfqRequest $rfq, MarkRfqAsReadAction $action): Response
    {
        if (! $rfq->is_read) {
            $action->handle($rfq);
        }

        return Inertia::render('admin/rfqs/show', [
            'rfq' => (new RfqRequestResource(
                $rfq->load(['product', 'containerType', 'notes.user']),
            ))->resolve(),
            'statuses' => RfqStatus::options(),
        ]);
    }

    public function updateStatus(
        Request $request,
        RfqRequest $rfq,
        UpdateRfqStatusAction $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(RfqStatus::class)],
        ]);

        $action->handle($rfq, RfqStatus::from($validated['status']));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Status RFQ berhasil diperbarui.',
        ]);

        return back();
    }

    public function storeNote(
        Request $request,
        RfqRequest $rfq,
        AddRfqNoteAction $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ], [
            'note.required' => 'Catatan tidak boleh kosong.',
        ]);

        $action->handle($rfq, $validated['note']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Catatan berhasil ditambahkan.',
        ]);

        return back();
    }

    public function markAsSpam(RfqRequest $rfq, MarkRfqAsSpamAction $action): RedirectResponse
    {
        $action->handle($rfq);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'RFQ ditandai sebagai spam.',
        ]);

        return back();
    }

    public function markAsUnread(RfqRequest $rfq, MarkRfqAsReadAction $action): RedirectResponse
    {
        $action->handle($rfq, false);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'RFQ ditandai belum dibaca.',
        ]);

        return back();
    }

    /**
     * Ekspor CSV mengikuti filter yang sedang aktif, bukan seluruh tabel.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only([
            'search', 'status', 'country_code', 'product_id', 'unread',
            'from', 'to', 'sort_by', 'sort_dir',
        ]);

        $rows = $this->rfqs->allMatching($filters);
        $fileName = 'rfq-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, array_values(self::EXPORT_COLUMNS));

            foreach ($rows as $rfq) {
                fputcsv($handle, [
                    $rfq->reference_no,
                    $rfq->created_at?->format('Y-m-d H:i'),
                    $rfq->company_name,
                    $rfq->contact_name,
                    $rfq->email,
                    $rfq->phone,
                    $rfq->whatsapp,
                    $rfq->country_code,
                    $rfq->incoterms?->value,
                    $rfq->port_of_destination,
                    $rfq->monthly_tonnage,
                    $rfq->status->label(),
                ]);
            }

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
