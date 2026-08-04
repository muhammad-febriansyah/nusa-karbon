<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\CertificateResource;
use App\Http\Resources\Public\PageBlockResource;
use App\Http\Resources\Public\ProductionStepResource;
use App\Http\Resources\Public\TeamMemberResource;
use App\Models\PageBlock;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use App\Repositories\Contracts\ProductionStepRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tentang kami.
 *
 * Menjawab pertanyaan yang tidak bisa dijawab lembar spesifikasi: siapa yang
 * mengerjakan, di posisi mana di rantai pasok, dan apa yang bisa diperiksa.
 */
final class AboutController extends Controller
{
    /**
     * Jumlah anggota tim per halaman roster.
     *
     * Delapan cukup untuk satu layar penuh tanpa menggulir, dan membuat tiga
     * puluh orang jadi empat halaman — bukan satu dinding wajah.
     */
    private const TEAM_PER_PAGE = 8;

    public function __construct(
        private readonly PageBlockRepositoryInterface $pageBlocks,
        private readonly TeamMemberRepositoryInterface $team,
        private readonly CertificateRepositoryInterface $certificates,
        private readonly ProductionStepRepositoryInterface $productionSteps,
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render('public/about', [
            'blocks' => $this->blocks(),
            /*
             * Roster berhalaman, bukan seluruh tim sekaligus.
             *
             * Tiga puluh kartu foto memakan setengah tinggi halaman dan tetap
             * tidak menjawab tugas pembacanya: pembeli datang untuk memeriksa
             * satu orang yang menghubunginya, bukan menelusuri semua wajah.
             */
            'team' => $this->teamRoster(),
            /*
             * Seluruh sertifikat, bukan hanya yang ditandai trust bar. Trust
             * bar di beranda memang disaring karena ruangnya sempit; halaman
             * ini justru tempat daftar lengkapnya diperiksa.
             */
            'certificates' => CertificateResource::collection(
                $this->certificates->activeOrdered(),
            )->resolve(),
            'productionSteps' => ProductionStepResource::collection(
                $this->productionSteps->activeOrdered(),
            )->resolve(),
        ]);
    }

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    private function teamRoster(): array
    {
        $page = $this->team->activePaginated(self::TEAM_PER_PAGE);

        return [
            // `resolve()` mempertahankan kunci koleksinya; tanpa penataan
            // ulang, kunci yang meloncat membuat `json_encode` mengirimnya
            // sebagai objek dan `team.data.map()` di React gagal.
            'data' => array_values(
                TeamMemberResource::collection($page->items())->resolve(),
            ),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'from' => $page->firstItem(),
                'to' => $page->lastItem(),
                'total' => $page->total(),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function blocks(): array
    {
        return $this->pageBlocks->activeForPage('about')
            ->mapWithKeys(fn (PageBlock $block): array => [
                $block->block_key => PageBlockResource::make($block)->resolve(),
            ])
            ->all();
    }
}
