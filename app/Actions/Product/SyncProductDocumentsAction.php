<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Enums\DocumentType;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Services\MediaUploadService;
use App\Support\Locale;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductDocumentsAction
{
    /**
     * Semua dokumen produk ditaruh di satu direktori pada disk `public`.
     */
    public const DIRECTORY = 'products/documents';

    public function __construct(private MediaUploadService $media) {}

    /**
     * Menyelaraskan dokumen produk dengan yang dikirim form.
     *
     * Baris ber-`id` diperbarui di tempat; baris tanpa `id` dibuat baru dan
     * wajib membawa token unggahan. Baris lama yang tidak ada di kiriman
     * di-soft-delete — bukan dihapus permanen — supaya riwayat unduhan pada
     * `document_downloads` tetap utuh.
     *
     * Datanya datang dari `validated()` yang bertipe longgar, jadi tiap kunci
     * dinormalkan di sini.
     *
     * @param  array<int, array<string, mixed>>  $documents
     */
    public function handle(Product $product, array $documents): void
    {
        DB::transaction(function () use ($product, $documents): void {
            $keptIds = [];

            foreach ($documents as $document) {
                $id = isset($document['id']) ? (int) $document['id'] : null;
                $token = isset($document['token']) && is_string($document['token'])
                    ? $document['token']
                    : null;

                $existing = $id !== null && $id > 0
                    ? $product->documents()->whereKey($id)->first()
                    : null;

                $file = $token !== null
                    ? $this->media->moveFromToken($token, self::DIRECTORY)
                    : null;

                // Dokumen baru tanpa berkas tidak punya arti — lewati saja.
                if ($existing === null && $file === null) {
                    continue;
                }

                $attributes = $this->attributes($document);

                if ($existing === null) {
                    /** @var array{path: string, name: string, size: int, mime: string} $file */
                    $created = $product->documents()->create($attributes + [
                        'file_path' => $file['path'],
                        'file_name' => $file['name'],
                        'file_size' => $file['size'],
                        'mime_type' => $file['mime'],
                    ]);

                    $keptIds[] = $created->id;

                    continue;
                }

                $previousPath = null;

                if ($file !== null) {
                    $previousPath = $existing->file_path;

                    $attributes += [
                        'file_path' => $file['path'],
                        'file_name' => $file['name'],
                        'file_size' => $file['size'],
                        'mime_type' => $file['mime'],
                    ];
                }

                $existing->update($attributes);

                // Berkas lama baru dihapus setelah baris menunjuk berkas baru.
                if ($previousPath !== null && $previousPath !== $existing->file_path) {
                    $this->media->delete($previousPath);
                }

                $keptIds[] = $existing->id;
            }

            $product->documents()
                ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
                ->each(fn (ProductDocument $document) => $document->delete());
        });
    }

    /**
     * Kolom metadata yang boleh diubah dari form.
     *
     * @param  array<string, mixed>  $document
     * @return array<string, mixed>
     */
    private function attributes(array $document): array
    {
        $type = DocumentType::tryFrom((string) ($document['type'] ?? ''))
            ?? DocumentType::Other;

        $title = $document['title'] ?? [];
        $title = is_array($title) ? $title : [];

        // Judul kosong diisi label tipenya supaya daftar unduhan publik tidak
        // menampilkan baris tanpa nama.
        if (($title[Locale::default()] ?? '') === '') {
            $title[Locale::default()] = $type->label();
        }

        $batch = $document['batch_reference'] ?? null;
        $issued = $document['issued_at'] ?? null;

        return [
            'type' => $type,
            'title' => $title,
            'batch_reference' => is_string($batch) && $batch !== '' ? $batch : null,
            'issued_at' => is_string($issued) && $issued !== '' ? $issued : null,
            'is_active' => (bool) ($document['is_active'] ?? true),
        ];
    }
}
