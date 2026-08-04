<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Enums\RfqStatus;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Product;
use App\Models\Representative;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use App\Support\Locale;
use Illuminate\Support\Facades\Cache;

/**
 * Menyusun angka dashboard. Query-nya di-cache lima menit: dashboard dibuka
 * berkali-kali sehari dan angkanya tidak perlu real-time.
 *
 * @see docs/09-tasks.md bagian 4.7
 */
final readonly class DashboardService
{
    private const CACHE_KEY = 'dashboard.summary';

    private const CACHE_TTL_SECONDS = 300;

    public function __construct(
        private RfqRequestRepositoryInterface $rfqs,
        private PostRepositoryInterface $posts,
        private CertificateRepositoryInterface $certificates,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->build(),
        );
    }

    /**
     * Dipanggil setelah data yang memengaruhi angka berubah.
     */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $monthly = $this->rfqs->monthlyTotals();
        $statusTotals = $this->rfqs->statusTotals();

        $thisMonth = $this->totalForMonth($monthly, now()->format('Y-m'));
        $lastMonth = $this->totalForMonth($monthly, now()->subMonth()->format('Y-m'));

        return [
            'stats' => [
                [
                    'key' => 'rfq_unread',
                    'label' => 'RFQ belum dibaca',
                    'value' => $this->rfqs->countUnread(),
                    'delta' => null,
                    'hint' => 'Menunggu dibalas',
                ],
                [
                    'key' => 'rfq_this_month',
                    'label' => 'RFQ bulan ini',
                    'value' => $thisMonth,
                    'delta' => $this->delta($thisMonth, $lastMonth),
                    'hint' => "Bulan lalu {$lastMonth}",
                ],
                [
                    'key' => 'products_active',
                    'label' => 'Produk aktif',
                    'value' => Product::query()->active()->count(),
                    'delta' => null,
                    'hint' => Product::query()->calculable()->count().' siap kalkulator',
                ],
                [
                    'key' => 'posts_published',
                    'label' => 'Artikel terbit',
                    'value' => $this->posts->countPublished(),
                    'delta' => null,
                    'hint' => Post::query()->where('status', PostStatus::Draft)->count().' masih draf',
                ],
            ],
            'monthlyRfqs' => $monthly,
            'topCountries' => $this->rfqs->topCountries(),
            'topProducts' => $this->rfqs->topProducts(),
            'funnel' => $this->funnel($statusTotals),
            'conversion' => $this->conversion($statusTotals),
            'content' => $this->contentSummary(),
            'attention' => $this->attention(),
        ];
    }

    /**
     * Corong status RFQ: berapa banyak permintaan berhenti di tiap tahap.
     * Warna hanya pelengkap — tiap baris tetap membawa label dan angkanya,
     * karena beberapa pasangan token warna berdekatan bagi mata buta warna.
     *
     * @param  array<string, int>  $totals
     * @return array<int, array{status: string, label: string, color: string, total: int, share: float}>
     */
    private function funnel(array $totals): array
    {
        $overall = array_sum($totals);

        return array_map(
            fn (RfqStatus $status): array => [
                'status' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
                'total' => $totals[$status->value] ?? 0,
                'share' => $overall === 0
                    ? 0.0
                    : round((($totals[$status->value] ?? 0) / $overall) * 100, 1),
            ],
            RfqStatus::cases(),
        );
    }

    /**
     * Rasio menang terhadap permintaan yang benar-benar diproses. Spam tidak
     * ikut dihitung supaya angkanya tidak terlihat lebih buruk dari kenyataan.
     *
     * @param  array<string, int>  $totals
     * @return array{won: int, lost: int, processed: int, rate: float}
     */
    private function conversion(array $totals): array
    {
        $won = $totals[RfqStatus::Won->value] ?? 0;
        $lost = $totals[RfqStatus::Lost->value] ?? 0;
        $processed = array_sum($totals) - ($totals[RfqStatus::Spam->value] ?? 0);

        return [
            'won' => $won,
            'lost' => $lost,
            'processed' => $processed,
            'rate' => $processed === 0 ? 0.0 : round(($won / $processed) * 100, 1),
        ];
    }

    /**
     * Ringkasan isi situs: apa yang sudah terisi dan apa yang masih kosong.
     *
     * @return array<int, array{key: string, label: string, value: int, hint: string}>
     */
    private function contentSummary(): array
    {
        return [
            [
                'key' => 'certificates',
                'label' => 'Sertifikat aktif',
                'value' => Certificate::query()->active()->count(),
                'hint' => $this->certificates->expiringWithin()->count().' segera kedaluwarsa',
            ],
            [
                'key' => 'representatives',
                'label' => 'Perwakilan resmi',
                'value' => Representative::query()->active()->count(),
                'hint' => 'Tampil di halaman verifikasi',
            ],
            [
                'key' => 'faqs',
                'label' => 'Pertanyaan FAQ',
                'value' => Faq::query()->active()->count(),
                'hint' => 'Mengurangi pertanyaan berulang',
            ],
            [
                'key' => 'galleries',
                'label' => 'Album galeri',
                'value' => Gallery::query()->active()->count(),
                'hint' => 'Bukti visual proses produksi',
            ],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, total: int, tone: string}>
     */
    private function attention(): array
    {
        $locale = Locale::default();

        $incompleteProducts = Product::query()
            ->active()
            ->where(function ($query): void {
                $query->whereNull('master_carton_weight_kg')
                    ->orWhereNull('master_carton_cbm');
            })
            ->count();

        $untranslated = Product::query()
            ->active()
            ->whereNull("name->{$locale}")
            ->count();

        $expiringCertificates = $this->certificates->expiringWithin()->count();
        $scheduledPosts = Post::query()->where('status', PostStatus::Scheduled)->count();

        return array_values(array_filter([
            $incompleteProducts === 0 ? null : [
                'key' => 'products_incomplete',
                'label' => 'Produk tanpa data master carton',
                'total' => $incompleteProducts,
                'tone' => 'warning',
            ],
            $expiringCertificates === 0 ? null : [
                'key' => 'certificates_expiring',
                'label' => 'Sertifikat kedaluwarsa dalam '.Certificate::EXPIRY_WARNING_DAYS.' hari',
                'total' => $expiringCertificates,
                'tone' => 'destructive',
            ],
            $untranslated === 0 ? null : [
                'key' => 'products_untranslated',
                'label' => 'Produk belum diterjemahkan',
                'total' => $untranslated,
                'tone' => 'info',
            ],
            $scheduledPosts === 0 ? null : [
                'key' => 'posts_scheduled',
                'label' => 'Artikel menunggu jadwal tayang',
                'total' => $scheduledPosts,
                'tone' => 'info',
            ],
        ]));
    }

    /**
     * Selisih persen terhadap bulan lalu. Null bila bulan lalu nol — persentase
     * terhadap nol tidak bermakna dan hanya menipu pembacanya.
     */
    private function delta(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @param  array<int, array{month: string, total: int}>  $monthly
     */
    private function totalForMonth(array $monthly, string $month): int
    {
        foreach ($monthly as $row) {
            if ($row['month'] === $month) {
                return $row['total'];
            }
        }

        return 0;
    }
}
