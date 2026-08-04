<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\RfqRequest;

/**
 * Isi lonceng notifikasi: permintaan penawaran dan pesan kontak yang belum
 * dibaca.
 *
 * Tidak memakai tabel notifikasi Laravel — semua yang perlu diberitahukan sudah
 * punya kolom `is_read` sendiri, dan menyalinnya ke tabel kedua hanya membuka
 * peluang dua sumber kebenaran yang berbeda.
 */
final readonly class AdminNotificationService
{
    private const LIMIT = 5;

    /**
     * @return array{
     *     total: int,
     *     items: array<int, array{
     *         id: string, type: string, title: string, subtitle: string,
     *         time: string, url: string
     *     }>
     * }
     */
    public function unread(): array
    {
        $rfqs = RfqRequest::query()
            ->unread()
            ->latest()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (RfqRequest $rfq): array => [
                'id' => 'rfq-'.$rfq->id,
                'type' => 'rfq',
                'title' => $rfq->company_name,
                'subtitle' => $rfq->reference_no.' · '.$rfq->country_code,
                'time' => $rfq->created_at?->diffForHumans() ?? '',
                'url' => route('admin.rfqs.show', $rfq),
            ]);

        $messages = ContactMessage::query()
            ->unread()
            ->latest()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (ContactMessage $message): array => [
                'id' => 'contact-'.$message->id,
                'type' => 'contact',
                'title' => $message->name,
                'subtitle' => $message->subject ?? $message->email,
                'time' => $message->created_at?->diffForHumans() ?? '',
                'url' => route('admin.contact-messages.index', ['search' => $message->email]),
            ]);

        return [
            'total' => RfqRequest::query()->unread()->count()
                + ContactMessage::query()->unread()->count(),
            'items' => $rfqs->concat($messages)->values()->all(),
        ];
    }
}
