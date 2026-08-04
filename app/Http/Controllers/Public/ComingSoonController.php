<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Penampung sementara untuk halaman publik yang rutenya sudah final tapi
 * isinya belum dikerjakan.
 *
 * Alasannya bukan kemalasan: URL publik ikut menentukan struktur `hreflang`,
 * sitemap, dan tautan internal. Menetapkannya lebih awal berarti navigasi,
 * Wayfinder, dan tautan di footer tidak pernah menunjuk ke halaman mati, dan
 * mengisi halamannya nanti tidak mengubah satu pun URL.
 *
 * Judul halaman datang dari `->defaults('page', …)` pada definisi rute.
 * Setiap rute yang isinya sudah jadi harus dipindahkan ke controllernya
 * sendiri — controller ini hanya boleh menyusut.
 */
final class ComingSoonController extends Controller
{
    public function __invoke(string $page): Response
    {
        return Inertia::render('public/coming-soon', [
            'page' => $page,
        ]);
    }
}
