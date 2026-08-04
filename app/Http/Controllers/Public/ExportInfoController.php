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
            'faqGroups' => $this->faqGroups(),
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
     * FAQ dikelompokkan per kategori.
     *
     * Pengelompokan dilakukan di sini, bukan di frontend, supaya urutan
     * kategori mengikuti urutan `faq_category_id` yang sudah disusun repository
     * — mengulangnya di React berarti dua tempat yang bisa berbeda urutan.
     *
     * Kategori yang seluruh pertanyaannya nonaktif tidak pernah muncul, karena
     * yang dikelompokkan hanya FAQ yang aktif.
     *
     * @return list<array{category: string, items: list<array{id: int, question: string|null, answer: string|null}>}>
     */
    private function faqGroups(): array
    {
        $locale = app()->getLocale();

        $groups = $this->faqs->activeWithCategory()
            ->groupBy(fn (Faq $faq): string => $faq->category?->getTranslation('name', $locale) ?: 'Lainnya')
            ->map(fn ($items, string $category): array => [
                'category' => $category,
                'items' => array_values($items
                    ->map(fn (Faq $faq): array => [
                        'id' => $faq->id,
                        'question' => $this->text($faq->getTranslation('question', $locale)),
                        'answer' => $this->text($faq->getTranslation('answer', $locale)),
                    ])
                    ->all()),
            ])
            ->values()
            ->all();

        return array_values($groups);
    }

    private function text(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
