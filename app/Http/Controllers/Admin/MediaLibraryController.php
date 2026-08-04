<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Media Library: satu daftar seluruh berkas pada disk publik beserta
 * pemakainya, plus pembersihan berkas yang tidak dipakai modul mana pun.
 *
 * @see docs/09-tasks.md bagian 4.4
 */
final class MediaLibraryController extends Controller
{
    public function __construct(private readonly MediaLibraryService $media) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'type', 'usage', 'from', 'to']);
        $files = $this->media->inventory($filters);

        return Inertia::render('admin/media/index', [
            'files' => $files,
            'summary' => [
                'total' => count($files),
                'orphans' => count(array_filter(
                    $files,
                    fn (array $file): bool => $file['usages'] === [],
                )),
                'size' => array_sum(array_column($files, 'size')),
            ],
            'filters' => $filters,
        ]);
    }

    /**
     * Daftar gambar untuk dialog pemilih di editor TipTap.
     */
    public function picker(Request $request): JsonResponse
    {
        $files = $this->media->inventory([
            'type' => 'image',
            'search' => $request->string('search')->toString(),
        ]);

        return response()->json([
            'files' => array_map(
                fn (array $file): array => [
                    'path' => $file['path'],
                    'name' => $file['name'],
                    'url' => $file['url'],
                    'dimensions' => $file['dimensions'],
                ],
                array_slice($files, 0, 60),
            ),
        ]);
    }

    /**
     * Menghapus berkas terpilih. Berkas yang masih dipakai ditolak beserta
     * daftar pemakainya, bukan dihapus lalu menyisakan tautan rusak.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'paths' => ['required', 'array', 'max:200'],
            'paths.*' => ['string'],
        ]);

        $result = $this->media->deleteUnused($validated['paths']);

        if ($result['blocked'] !== []) {
            $names = array_map(
                static fn (string $path): string => basename($path),
                $result['blocked'],
            );

            return back()->withErrors([
                'media' => 'Berkas berikut masih dipakai dan tidak dihapus: '.implode(', ', $names).'.',
            ]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => count($result['deleted']).' berkas berhasil dihapus.',
        ]);

        return back();
    }

    /**
     * Membersihkan seluruh berkas yang tidak dipakai modul mana pun.
     */
    public function pruneOrphans(): RedirectResponse
    {
        $paths = array_column($this->media->orphans(), 'path');
        $result = $this->media->deleteUnused($paths);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => count($result['deleted']).' berkas yang tidak dipakai berhasil dihapus.',
        ]);

        return back();
    }
}
