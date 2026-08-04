<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UploadKind;
use App\Support\SvgSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;

/**
 * Menyimpan berkas unggahan ke disk `public` dengan nama yang sudah
 * disanitasi, dan membereskan berkas lama saat diganti.
 *
 * Konversi ukuran gambar ditangani `spatie/laravel-medialibrary` lewat
 * `registerMediaConversions()` pada model yang memakai koleksi media.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.4
 */
final class MediaUploadService
{
    /**
     * Harus sama dengan `TempUploadController::DIRECTORY`.
     */
    private const TEMP_DIRECTORY = 'tmp-uploads';

    public function __construct(private readonly SvgSanitizer $svg) {}

    /**
     * Menyimpan berkas dan mengembalikan path relatif pada disk `public`.
     */
    public function store(UploadedFile $file, UploadKind $kind, string $directory): string
    {
        $name = $this->fileName($file);

        if ($this->isSvg($file)) {
            if (! $kind->allowsSvg()) {
                throw new RuntimeException('SVG hanya diterima untuk logo.');
            }

            return $this->storeSanitizedSvg($file, $directory, $name);
        }

        $path = $file->storeAs($directory, $name, 'public');

        if ($path === false) {
            throw new RuntimeException('Berkas gagal disimpan.');
        }

        return $path;
    }

    /**
     * Menyimpan berkas baru lalu menghapus yang lama. Berkas lama hanya
     * dihapus setelah yang baru berhasil ditulis.
     */
    public function replace(
        ?string $currentPath,
        UploadedFile $file,
        UploadKind $kind,
        string $directory,
    ): string {
        $path = $this->store($file, $kind, $directory);

        if ($currentPath !== null && $currentPath !== $path) {
            $this->delete($currentPath);
        }

        return $path;
    }

    /**
     * Memindahkan berkas unggahan sementara ke koleksi media sebuah model.
     * Token yang tidak ditemukan diabaikan agar submit form tidak gagal hanya
     * karena berkas sementara sudah kedaluwarsa.
     *
     * @param  array<int, string>  $tokens
     */
    public function attachFromTokens(
        HasMedia $model,
        array $tokens,
        string $collection,
        bool $replaceExisting = false,
    ): void {
        if ($replaceExisting && $tokens !== []) {
            $model->clearMediaCollection($collection);
        }

        foreach ($tokens as $token) {
            if (! Str::isUlid($token)) {
                continue;
            }

            $directory = self::TEMP_DIRECTORY.'/'.$token;
            $files = Storage::disk('local')->files($directory);

            if ($files === []) {
                continue;
            }

            $model->addMedia(Storage::disk('local')->path($files[0]))
                ->toMediaCollection($collection);

            Storage::disk('local')->deleteDirectory($directory);
        }
    }

    /**
     * Memindahkan satu berkas sementara ke disk `public` dan mengembalikan
     * metadatanya. Dipakai berkas yang tidak masuk media library — dokumen
     * produk misalnya, yang metadatanya disimpan di tabel sendiri.
     *
     * Mengembalikan null bila token tidak valid atau berkasnya sudah hilang,
     * supaya submit form tidak gagal hanya karena berkas sementara kedaluwarsa.
     *
     * @return array{path: string, name: string, size: int, mime: string}|null
     */
    public function moveFromToken(string $token, string $directory): ?array
    {
        if (! Str::isUlid($token)) {
            return null;
        }

        $temp = Storage::disk('local');
        $tempDirectory = self::TEMP_DIRECTORY.'/'.$token;
        $files = $temp->files($tempDirectory);

        if ($files === []) {
            return null;
        }

        $source = $files[0];
        $name = basename($source);
        $path = $directory.'/'.$name;

        $stream = $temp->readStream($source);

        if ($stream === null) {
            return null;
        }

        Storage::disk('public')->writeStream($path, $stream);

        if (is_resource($stream)) {
            fclose($stream);
        }

        $size = $temp->size($source);
        $mime = $temp->mimeType($source);

        $temp->deleteDirectory($tempDirectory);

        return [
            'path' => $path,
            'name' => $name,
            'size' => $size,
            'mime' => $mime === false ? 'application/octet-stream' : $mime,
        ];
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '') {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Nama berkas disanitasi: huruf kecil, spasi jadi tanda hubung, karakter
     * non-ASCII dibuang, ditambah hash pendek untuk mencegah tabrakan.
     */
    public function fileName(UploadedFile $file): string
    {
        $original = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME,
        );

        $slug = Str::slug(Str::ascii($original));

        if ($slug === '') {
            $slug = 'berkas';
        }

        $slug = Str::limit($slug, 60, '');
        $extension = Str::lower($file->getClientOriginalExtension());

        return $slug.'-'.Str::lower(Str::random(8)).'.'.$extension;
    }

    private function isSvg(UploadedFile $file): bool
    {
        return Str::lower($file->getClientOriginalExtension()) === 'svg'
            || $file->getMimeType() === 'image/svg+xml';
    }

    private function storeSanitizedSvg(
        UploadedFile $file,
        string $directory,
        string $name,
    ): string {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Berkas SVG tidak dapat dibaca.');
        }

        $clean = $this->svg->sanitize($contents);

        if ($clean === null) {
            throw new RuntimeException('Berkas SVG tidak valid.');
        }

        $path = $directory.'/'.$name;

        Storage::disk('public')->put($path, $clean);

        return $path;
    }
}
