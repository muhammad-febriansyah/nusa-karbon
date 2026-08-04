<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TempUploadRequest;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Unggahan sementara satu berkas. Dipisah dari submit form agar uploader bisa
 * menampilkan progress per berkas dan membatalkan unggahan yang berjalan.
 *
 * Berkas disimpan di disk privat; form menyimpan tokennya, lalu berkas
 * dipindahkan ke tujuan akhir saat form disimpan.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
final class TempUploadController extends Controller
{
    /**
     * Harus sama dengan `MediaUploadService::TEMP_DIRECTORY`.
     */
    private const DIRECTORY = 'tmp-uploads';

    public function store(TempUploadRequest $request, MediaUploadService $media): JsonResponse
    {
        $file = $request->file('file');
        $token = (string) Str::ulid();

        try {
            $name = $media->fileName($file);

            $file->storeAs(self::DIRECTORY.'/'.$token, $name, 'local');
        } catch (RuntimeException $exception) {
            return response()->json(
                ['message' => $exception->getMessage()],
                422,
            );
        }

        return response()->json([
            'token' => $token,
            'name' => $file->getClientOriginalName(),
            'stored_name' => $name,
            'size' => $file->getSize(),
        ], 201);
    }

    public function destroy(Request $request, string $token): JsonResponse
    {
        // Token berformat ULID, jadi tidak bisa dipakai menembus direktori.
        if (Str::isUlid($token)) {
            Storage::disk('local')->deleteDirectory(self::DIRECTORY.'/'.$token);
        }

        return response()->json(status: 204);
    }
}
