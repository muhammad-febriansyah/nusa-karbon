<?php

declare(strict_types=1);

use App\Actions\Product\SyncProductDocumentsAction;
use App\Enums\DocumentType;
use App\Models\DocumentDownload;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->user = User::factory()->create();
    $this->sync = app(SyncProductDocumentsAction::class);
});

/**
 * Mengunggah berkas ke endpoint sementara dan mengembalikan tokennya, persis
 * seperti yang dilakukan `DocumentUploader` sebelum form disimpan.
 */
function uploadDocumentToken(string $name = 'coa.pdf', int $kilobytes = 500, string $mime = 'application/pdf'): string
{
    return test()->actingAs(test()->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'document',
            'file' => UploadedFile::fake()->create($name, $kilobytes, $mime),
        ])
        ->assertCreated()
        ->json('token');
}

test('hanya pdf dan xlsx yang diterima sebagai dokumen', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'document',
            'file' => UploadedFile::fake()->create('gambar.jpg', 100, 'image/jpeg'),
        ])
        ->assertStatus(422)
        ->assertJsonPath(
            'errors.file.0',
            'Format berkas belum didukung. Gunakan: PDF, XLSX.',
        );
});

test('dokumen xlsx diterima', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'document',
            'file' => UploadedFile::fake()->create(
                'packing-list.xlsx',
                500,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ),
        ])
        ->assertCreated();
});

test('dokumen di atas 10 MB ditolak', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'document',
            'file' => UploadedFile::fake()->create('coa.pdf', 11 * 1024, 'application/pdf'),
        ])
        ->assertStatus(422)
        ->assertJsonPath(
            'errors.file.0',
            'Ukuran berkas melebihi batas 10 MB. Kompres berkas terlebih dahulu.',
        );
});

test('dokumen ikut tersimpan saat produk dibuat', function () {
    $token = uploadDocumentToken();

    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'DOC-01',
            'name' => ['id' => 'Produk Dokumen'],
            'slug' => ['id' => 'produk-dokumen'],
            'documents' => [
                [
                    'type' => 'coa',
                    'title' => ['id' => 'Certificate of Analysis'],
                    'batch_reference' => 'SP-2409',
                    'issued_at' => '2026-01-15',
                    'is_active' => true,
                    'token' => $token,
                ],
            ],
        ])
        ->assertSessionHasNoErrors();

    $document = ProductDocument::query()->first();

    expect($document)->not->toBeNull()
        ->and($document->type)->toBe(DocumentType::Coa)
        ->and($document->getTranslation('title', 'id'))->toBe('Certificate of Analysis')
        ->and($document->batch_reference)->toBe('SP-2409')
        ->and($document->issued_at->format('Y-m-d'))->toBe('2026-01-15')
        ->and($document->mime_type)->toBe('application/pdf');

    Storage::disk('public')->assertExists($document->file_path);
    // Berkas sementara sudah dipindahkan, bukan disalin.
    expect(Storage::disk('local')->exists('tmp-uploads/'.$token))->toBeFalse();
});

test('tipe dokumen wajib salah satu dari daftar', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'DOC-02',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'documents' => [
                ['type' => 'sertifikat', 'title' => ['id' => 'Apa saja']],
            ],
        ])
        ->assertSessionHasErrors('documents.0.type');
});

test('judul dokumen wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'DOC-03',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'documents' => [
                ['type' => 'coa', 'title' => ['id' => '']],
            ],
        ])
        ->assertSessionHasErrors([
            'documents.0.title.id' => 'Judul dokumen wajib diisi.',
        ]);
});

test('tanggal terbit harus berformat tanggal', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'DOC-04',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'documents' => [
                [
                    'type' => 'coa',
                    'title' => ['id' => 'COA'],
                    'issued_at' => '15 Januari 2026',
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'documents.0.issued_at' => 'Tanggal terbit tidak valid.',
        ]);
});

test('baris dokumen tanpa berkas diabaikan', function () {
    $product = Product::factory()->create();

    $this->sync->handle($product, [
        ['type' => 'coa', 'title' => ['id' => 'COA tanpa berkas']],
    ]);

    expect($product->documents()->count())->toBe(0);
});

test('mengganti berkas menghapus berkas lama', function () {
    $product = Product::factory()->create();

    $this->sync->handle($product, [
        [
            'type' => 'coa',
            'title' => ['id' => 'COA'],
            'token' => uploadDocumentToken('lama.pdf'),
        ],
    ]);

    $document = $product->documents()->first();
    $oldPath = $document->file_path;

    $this->sync->handle($product, [
        [
            'id' => $document->id,
            'type' => 'coa',
            'title' => ['id' => 'COA'],
            'token' => uploadDocumentToken('baru.pdf'),
        ],
    ]);

    $document->refresh();

    expect($document->file_path)->not->toBe($oldPath)
        ->and($document->file_name)->toStartWith('baru-');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($document->file_path);
});

test('menyimpan ulang tanpa berkas baru mempertahankan berkas lama', function () {
    $product = Product::factory()->create();

    $this->sync->handle($product, [
        [
            'type' => 'coa',
            'title' => ['id' => 'COA'],
            'token' => uploadDocumentToken(),
        ],
    ]);

    $document = $product->documents()->first();
    $path = $document->file_path;

    $this->sync->handle($product, [
        [
            'id' => $document->id,
            'type' => 'msds',
            'title' => ['id' => 'MSDS'],
            'batch_reference' => 'SP-2501',
        ],
    ]);

    $document->refresh();

    expect($product->documents()->count())->toBe(1)
        ->and($document->file_path)->toBe($path)
        ->and($document->type)->toBe(DocumentType::Msds)
        ->and($document->batch_reference)->toBe('SP-2501');

    Storage::disk('public')->assertExists($path);
});

test('dokumen yang dihapus dari form di-soft-delete agar riwayat unduhan utuh', function () {
    $product = Product::factory()->create();
    $document = ProductDocument::factory()->create(['product_id' => $product->id]);
    DocumentDownload::factory()->create(['product_document_id' => $document->id]);

    $this->sync->handle($product, []);

    expect($product->documents()->count())->toBe(0)
        ->and(ProductDocument::withTrashed()->whereKey($document->id)->exists())->toBeTrue()
        ->and(DocumentDownload::query()->count())->toBe(1);
});

test('memperbarui produk tanpa mengirim dokumen tidak menghapusnya', function () {
    $product = Product::factory()->create();
    ProductDocument::factory()->count(2)->create(['product_id' => $product->id]);

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
        ])
        ->assertSessionHasNoErrors();

    expect($product->documents()->count())->toBe(2);
});

test('menghapus produk permanen ikut menghapus dokumennya', function () {
    $product = Product::factory()->create();
    ProductDocument::factory()->count(2)->create(['product_id' => $product->id]);

    $product->forceDelete();

    expect(ProductDocument::withTrashed()->where('product_id', $product->id)->count())
        ->toBe(0);
});

test('menghapus dokumen ikut menghapus catatan unduhannya', function () {
    $document = ProductDocument::factory()->create();
    DocumentDownload::factory()->count(3)->create([
        'product_document_id' => $document->id,
    ]);

    $document->forceDelete();

    expect(DocumentDownload::query()->count())->toBe(0);
});

test('catatan unduhan tidak menyimpan updated_at', function () {
    $download = DocumentDownload::factory()->create();

    expect($download->created_at)->not->toBeNull()
        ->and($download->getAttributes())->not->toHaveKey('updated_at');
});

test('pilihan tipe dokumen dikirim ke halaman form', function () {
    $this->actingAs($this->user)
        ->get(route('admin.products.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('documentTypes', 5)
            ->where('documentTypes.0.value', 'coa')
            ->where('documentTypes.0.label', 'COA'),
        );
});

test('dokumen ikut dikirim ke halaman ubah', function () {
    $product = Product::factory()->create();
    ProductDocument::factory()->create([
        'product_id' => $product->id,
        'title' => ['id' => 'COA Batch SP-2409'],
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.products.edit', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('product.documents', 1)
            ->where('product.documents.0.title.id', 'COA Batch SP-2409')
            ->where('product.documents.0.type_label', 'COA'),
        );
});
