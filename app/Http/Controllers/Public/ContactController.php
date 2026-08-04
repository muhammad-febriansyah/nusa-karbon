<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Rfq\CreateRfqRequestAction;
use App\Enums\Incoterm;
use App\Enums\RfqStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreRfqRequest;
use App\Models\ContainerType;
use App\Models\Product;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Formulir permintaan penawaran.
 *
 * Ini tujuan hampir seluruh tombol ajakan di situs, jadi halamannya sengaja
 * tidak menawarkan hal lain — pengunjung yang sampai ke sini sudah memutuskan
 * untuk menghubungi.
 */
final class ContactController extends Controller
{
    /**
     * Batas bawaan bila admin belum mengisi `rfq_rate_limit_per_hour`.
     *
     * Cukup longgar untuk pembeli yang mengirim beberapa permintaan berbeda
     * dalam satu sesi, cukup ketat untuk menghentikan robot yang mengirim
     * puluhan kali per menit.
     */
    private const DEFAULT_HOURLY_LIMIT = 5;

    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ContainerTypeRepositoryInterface $containers,
        private readonly SettingService $settings,
        private readonly CreateRfqRequestAction $createRfq,
    ) {}

    public function show(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('public/contact', [
            'products' => $this->products->activeForPublic()
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name', $locale) ?: $product->code,
                ])
                ->values()
                ->all(),
            'containers' => $this->containers->activeOrdered()
                ->map(fn (ContainerType $container): array => [
                    'id' => $container->id,
                    'name' => $container->getTranslation('name', $locale),
                ])
                ->values()
                ->all(),
            'incoterms' => Incoterm::options(),
        ]);
    }

    public function store(StoreRfqRequest $request): RedirectResponse
    {
        /*
         * Dibatasi per alamat IP, bukan per email: alamat email pada form
         * publik bisa diganti-ganti setiap kirim, sedangkan IP jauh lebih mahal
         * untuk dirotasi. Batasnya diambil dari pengaturan situs supaya admin
         * bisa melonggarkan saat pameran dagang tanpa menyentuh kode.
         */
        $key = 'rfq:'.$request->ip();
        $limit = $this->hourlyLimit();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return back()->withErrors([
                'email' => "Terlalu banyak permintaan dari jaringan ini. Coba lagi dalam {$minutes} menit, atau hubungi kami lewat email.",
            ]);
        }

        RateLimiter::increment($key, 3600);

        $rfq = $this->createRfq->handle([
            ...$request->safe()->except('website'),
            'status' => RfqStatus::New,
            'is_read' => false,
            'locale' => app()->getLocale(),
            /*
             * Jejak asal permintaan. Dipakai tim sales untuk tahu halaman mana
             * yang menghasilkan lead, dan dipakai admin untuk menelusuri
             * gelombang spam ke satu sumber.
             */
            'source_url' => $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $this->notificationRecipients());

        return back()->with('success', "Permintaan Anda tercatat dengan nomor {$rfq->reference_no}. Tim kami membalas dalam 24 jam kerja.");
    }

    private function hourlyLimit(): int
    {
        $limit = $this->settings->get('rfq_rate_limit_per_hour');

        return is_numeric($limit) && (int) $limit > 0
            ? (int) $limit
            : self::DEFAULT_HOURLY_LIMIT;
    }

    /**
     * Alamat tujuan pemberitahuan, dipisah koma di pengaturan situs.
     *
     * @return list<string>
     */
    private function notificationRecipients(): array
    {
        $raw = $this->settings->get('rfq_notification_recipients');

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $emails = array_filter(
            array_map(trim(...), explode(',', $raw)),
            fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        );

        return array_values($emails);
    }
}
