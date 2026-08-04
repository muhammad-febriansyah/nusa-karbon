<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Verifikasi perwakilan resmi.
 *
 * Pemasok briket kerap ditiru akun yang mengaku perwakilan lalu meminta
 * transfer ke rekening pribadi. Halaman ini memberi pembeli cara memastikan
 * sendiri: cari nama, email, atau nomor yang menghubunginya, lalu lihat apakah
 * orang itu memang terdaftar dan masih aktif.
 */
final class RepresentativeController extends Controller
{
    /** Panjang minimum kata kunci sebelum pencarian dijalankan. */
    private const MIN_QUERY_LENGTH = 3;

    public function __construct(
        private readonly RepresentativeRepositoryInterface $representatives,
    ) {}

    public function __invoke(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $searched = mb_strlen($query) >= self::MIN_QUERY_LENGTH;

        return Inertia::render('public/verify-representative', [
            'query' => $query,
            'searched' => $searched,
            /*
             * Daftar lengkap tidak pernah dikirim. Nama, email, dan nomor
             * seluruh tim adalah bahan mentah yang berguna justru bagi penipu —
             * pengunjung harus sudah memegang satu identitas untuk memeriksanya,
             * dan itulah persis alur yang dilindungi halaman ini.
             */
            'results' => $searched
                ? $this->search($query)
                : [],
            'minQueryLength' => self::MIN_QUERY_LENGTH,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function search(string $query): array
    {
        $locale = app()->getLocale();

        $results = $this->representatives->searchActive($query)
            ->map(fn (Representative $person): array => [
                'id' => $person->id,
                'name' => $person->name,
                'position' => $person->getTranslation('position', $locale) ?: null,
                'region' => $person->region,
                'photo_url' => $person->photoUrl(),
                /*
                 * Email dan nomor sengaja tidak dikirim balik. Halaman ini
                 * menjawab "apakah orang ini resmi", bukan "berikan kontak
                 * orang ini" — mengembalikannya akan mengubah alat verifikasi
                 * jadi direktori kontak yang bisa dipanen.
                 */
                'started_at' => $person->started_at?->toDateString(),
                'ended_at' => $person->ended_at?->toDateString(),
                'is_current' => $person->ended_at === null,
            ])
            ->values()
            ->all();

        return array_values($results);
    }
}
