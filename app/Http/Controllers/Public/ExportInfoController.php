<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PageBlockResource;
use App\Http\Resources\Public\QcProtocolResource;
use App\Http\Resources\Public\ShippingPartnerResource;
use App\Models\Faq;
use App\Models\PageBlock;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use App\Repositories\Contracts\QcProtocolRepositoryInterface;
use App\Repositories\Contracts\ShippingPartnerRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Info ekspor: syarat berdagang, standar mutu, dan pertanyaan yang berulang.
 *
 * Halaman ini menjawab pertanyaan yang muncul setelah pembeli puas dengan
 * produknya — MOQ, termin bayar, termin kirim, dokumen, dan ambang mutu yang
 * dijanjikan. Semuanya dari admin; tidak ada janji yang ditulis di kode.
 */
final class ExportInfoController extends Controller
{
    public function __construct(
        private readonly PageBlockRepositoryInterface $pageBlocks,
        private readonly QcProtocolRepositoryInterface $qcProtocols,
        private readonly FaqRepositoryInterface $faqs,
        private readonly ShippingPartnerRepositoryInterface $shippingPartners,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/export-info', [
            'blocks' => $this->blocks(),
            'qcProtocols' => QcProtocolResource::collection(
                $this->qcProtocols->activeOrdered(),
            )->resolve(),
            'faqs' => $this->faqs(),
            'shippingPartners' => ShippingPartnerResource::collection(
                $this->shippingPartners->activeOrdered(),
            )->resolve(),
        ]);
    }

    /**
     * Blok halaman `export_info`, dipetakan berdasarkan `block_key`.
     *
     * @return array<string, array<string, mixed>>
     */
    private function blocks(): array
    {
        return $this->pageBlocks->activeForPage('export_info')
            ->mapWithKeys(fn (PageBlock $block): array => [
                $block->block_key => PageBlockResource::make($block)->resolve(),
            ])
            ->all();
    }

    /**
     * Seluruh FAQ aktif sebagai satu daftar rata, kategorinya jadi keterangan.
     *
     * Sebelumnya dikelompokkan per kategori dengan judul sendiri-sendiri. Basis
     * data memuat 37 pertanyaan yang tersebar di 30 kategori, dan 24 di
     * antaranya berisi tepat satu pertanyaan — jadi pembaca melewati satu judul
     * untuk membaca satu jawaban, tiga puluh kali. Sebagai keterangan kecil di
     * baris pertanyaannya, kategori itu tetap memberi tahu konteks tanpa
     * memotong daftarnya jadi tiga puluh potongan.
     *
     * Urutannya tetap dari repository, bukan disusun ulang di React.
     *
     * @return list<array{id: int, category: string, question: string|null, answer: string|null}>
     */
    private function faqs(): array
    {
        $locale = app()->getLocale();

        return array_values($this->faqs->activeWithCategory()
            ->map(fn (Faq $faq): array => [
                'id' => $faq->id,
                'category' => $faq->category?->getTranslation('name', $locale) ?: 'Lainnya',
                'question' => $this->text($faq->getTranslation('question', $locale)),
                'answer' => $this->text($faq->getTranslation('answer', $locale)),
            ])
            ->all());
    }

    private function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
