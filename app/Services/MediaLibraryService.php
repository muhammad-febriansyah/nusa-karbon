<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Inventaris berkas pada disk `public` beserta siapa yang memakainya.
 *
 * Berkas di proyek ini datang dari dua jalur: kolom path biasa pada tabel
 * modul, dan `spatie/laravel-medialibrary` untuk galeri produk. Keduanya
 * disatukan di sini supaya admin melihat satu daftar, bukan dua.
 *
 * @see docs/09-tasks.md bagian 4.4
 */
final class MediaLibraryService
{
    /**
     * Kolom yang menyimpan path berkas: tabel => [kolom => label modul].
     * Menambah modul berarti menambah entri di sini, kalau tidak berkasnya
     * akan terbaca sebagai yatim dan bisa terhapus massal.
     *
     * @var array<string, array{label: string, name_column: string|null, columns: array<int, string>}>
     */
    private const PATH_COLUMNS = [
        'certificates' => ['label' => 'Sertifikat', 'name_column' => null, 'columns' => ['logo_path', 'file_path']],
        'product_documents' => ['label' => 'Dokumen Produk', 'name_column' => 'file_name', 'columns' => ['file_path']],
        'representatives' => ['label' => 'Perwakilan Resmi', 'name_column' => 'name', 'columns' => ['photo_path']],
        'production_steps' => ['label' => 'Proses Produksi', 'name_column' => null, 'columns' => ['image_path']],
        'qc_protocols' => ['label' => 'Protokol QC', 'name_column' => null, 'columns' => ['image_path']],
        'features' => ['label' => 'Keunggulan', 'name_column' => null, 'columns' => ['image_path']],
        'shipping_partners' => ['label' => 'Mitra Pelayaran', 'name_column' => 'name', 'columns' => ['logo_path']],
        'team_members' => ['label' => 'Tim', 'name_column' => 'name', 'columns' => ['photo_path']],
        'packaging_options' => ['label' => 'Opsi Kemasan', 'name_column' => null, 'columns' => ['image_path']],
        'galleries' => ['label' => 'Album Galeri', 'name_column' => null, 'columns' => ['cover_path']],
        'gallery_items' => ['label' => 'Isi Galeri', 'name_column' => null, 'columns' => ['file_path']],
        'page_blocks' => ['label' => 'Blok Halaman', 'name_column' => 'block_key', 'columns' => ['image_path']],
        'posts' => ['label' => 'Artikel', 'name_column' => null, 'columns' => ['cover_path']],
        'users' => ['label' => 'Pengguna', 'name_column' => 'name', 'columns' => ['avatar_path']],
    ];

    /**
     * Ekstensi yang dianggap gambar; sisanya masuk kelompok dokumen.
     *
     * @var list<string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'avif'];

    /**
     * Direktori yang tidak pernah ditampilkan: berkas sementara dan hasil
     * konversi medialibrary yang terikat pada berkas induknya.
     *
     * @var list<string>
     */
    private const HIDDEN_SEGMENTS = ['tmp-uploads', 'conversions', 'responsive-images'];

    /**
     * Seluruh berkas beserta pemakainya.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function inventory(array $filters = []): array
    {
        $usage = $this->usageMap();
        $disk = Storage::disk('public');
        $files = [];

        foreach ($disk->allFiles() as $path) {
            if ($this->isHidden($path)) {
                continue;
            }

            $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
            $type = in_array($extension, self::IMAGE_EXTENSIONS, true) ? 'image' : 'document';
            $modified = Carbon::createFromTimestamp($disk->lastModified($path));

            $files[] = [
                'path' => $path,
                'name' => basename($path),
                'directory' => dirname($path) === '.' ? '' : dirname($path),
                'url' => $disk->url($path),
                'type' => $type,
                'extension' => $extension,
                'size' => $disk->size($path),
                'dimensions' => $type === 'image' ? $this->dimensions($path) : null,
                'modified_at' => $modified->format('d M Y, H:i'),
                'modified_date' => $modified->toDateString(),
                'usages' => $usage[$path] ?? [],
            ];
        }

        return $this->applyFilters($files, $filters);
    }

    /**
     * Berkas yang tidak dirujuk baris mana pun.
     *
     * @return array<int, array<string, mixed>>
     */
    public function orphans(): array
    {
        return array_values(array_filter(
            $this->inventory(),
            fn (array $file): bool => $file['usages'] === [],
        ));
    }

    /**
     * @return array<int, array{module: string, label: string}>
     */
    public function usagesFor(string $path): array
    {
        return $this->usageMap()[$path] ?? [];
    }

    /**
     * Menghapus berkas yang tidak dipakai. Berkas yang masih dirujuk
     * dilewati dan dilaporkan balik, bukan dihapus diam-diam.
     *
     * @param  array<int, string>  $paths
     * @return array{deleted: array<int, string>, blocked: array<int, string>}
     */
    public function deleteUnused(array $paths): array
    {
        $usage = $this->usageMap();
        $disk = Storage::disk('public');

        $deleted = [];
        $blocked = [];

        foreach ($paths as $path) {
            if ($this->isHidden($path) || ! $disk->exists($path)) {
                continue;
            }

            if (($usage[$path] ?? []) !== []) {
                $blocked[] = $path;

                continue;
            }

            $disk->delete($path);
            $deleted[] = $path;
        }

        return ['deleted' => $deleted, 'blocked' => $blocked];
    }

    /**
     * Peta path berkas ke daftar entitas yang memakainya.
     *
     * @return array<string, array<int, array{module: string, label: string}>>
     */
    private function usageMap(): array
    {
        $map = [];

        foreach (self::PATH_COLUMNS as $table => $config) {
            $columns = $config['columns'];
            $nameColumn = $config['name_column'];
            $select = array_merge(['id'], $columns, $nameColumn === null ? [] : [$nameColumn]);

            foreach (DB::table($table)->select($select)->get() as $row) {
                foreach ($columns as $column) {
                    $path = $row->{$column} ?? null;

                    if (! is_string($path) || $path === '') {
                        continue;
                    }

                    $name = $nameColumn === null ? null : ($row->{$nameColumn} ?? null);

                    $map[$path][] = [
                        'module' => $config['label'],
                        'label' => is_string($name) && $name !== ''
                            ? $name
                            : "#{$row->id}",
                    ];
                }
            }
        }

        // Galeri produk memakai medialibrary; pathnya {id}/{nama berkas}.
        foreach (Media::query()->get(['id', 'file_name', 'model_type', 'collection_name']) as $media) {
            $map["{$media->id}/{$media->file_name}"][] = [
                'module' => Str::headline(class_basename($media->model_type)),
                'label' => $media->collection_name,
            ];
        }

        // Pengaturan situs menyimpan pathnya di dalam JSON.
        foreach (SiteSetting::query()->get() as $setting) {
            $value = $setting->plainValue();

            if (is_string($value) && $value !== '' && str_contains($value, '/')) {
                $map[$value][] = ['module' => 'Pengaturan Situs', 'label' => $setting->label];
            }
        }

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $files
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    private function applyFilters(array $files, array $filters): array
    {
        $search = $filters['search'] ?? null;
        $type = $filters['type'] ?? null;
        $usage = $filters['usage'] ?? null;
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        // `array_values` supaya kunci kembali berurutan setelah penyaringan;
        // JSON butuh larik, bukan objek berindeks lompat-lompat.
        $filtered = array_values(array_filter($files, function (array $file) use ($search, $type, $usage, $from, $to): bool {
            if (is_string($search) && $search !== ''
                && ! Str::contains(Str::lower($file['path']), Str::lower($search))) {
                return false;
            }

            if (is_string($type) && $type !== '' && $file['type'] !== $type) {
                return false;
            }

            if ($usage === 'orphan' && $file['usages'] !== []) {
                return false;
            }

            if ($usage === 'used' && $file['usages'] === []) {
                return false;
            }

            if (is_string($from) && $from !== '' && $file['modified_date'] < $from) {
                return false;
            }

            if (is_string($to) && $to !== '' && $file['modified_date'] > $to) {
                return false;
            }

            return true;
        }));

        usort(
            $filtered,
            fn (array $a, array $b): int => $b['modified_date'] <=> $a['modified_date']
                ?: strcmp($a['path'], $b['path']),
        );

        return $filtered;
    }

    private function isHidden(string $path): bool
    {
        foreach (self::HIDDEN_SEGMENTS as $segment) {
            if (Str::contains($path, $segment.'/') || Str::startsWith($path, $segment)) {
                return true;
            }
        }

        // Berkas sistem seperti `.gitignore` bawaan Laravel dan `.DS_Store`
        // bukan aset yang dikelola admin. Menampilkannya membuatnya terbaca
        // yatim, dan pembersihan massal akan menghapusnya — `.gitignore` di
        // `storage/app/public` justru yang menjaga direktori itu tetap ada.
        return Str::startsWith(basename($path), '.');
    }

    /**
     * @return array{width: int, height: int}|null
     */
    private function dimensions(string $path): ?array
    {
        $fullPath = Storage::disk('public')->path($path);

        if (! is_file($fullPath)) {
            return null;
        }

        $size = @getimagesize($fullPath);

        if ($size === false) {
            return null;
        }

        return ['width' => $size[0], 'height' => $size[1]];
    }
}
