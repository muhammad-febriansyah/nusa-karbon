<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengunggah', function () {
    $this->postJson(route('admin.uploads.temp.store'), [
        'kind' => 'gallery',
        'file' => UploadedFile::fake()->image('foto.jpg', 900, 900),
    ])->assertUnauthorized();
});

test('gambar galeri yang sah diterima dan mengembalikan token', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'gallery',
            'file' => UploadedFile::fake()->image('foto produk.jpg', 900, 900),
        ]);

    $response->assertCreated()
        ->assertJsonStructure(['token', 'name', 'stored_name', 'size']);

    $token = $response->json('token');

    Storage::disk('local')->assertExists(
        'tmp-uploads/'.$token.'/'.$response->json('stored_name'),
    );

    expect($response->json('name'))->toBe('foto produk.jpg');
});

test('format berkas yang tidak didukung ditolak dengan pesan Bahasa Indonesia', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'gallery',
            'file' => UploadedFile::fake()->create('data.txt', 10, 'text/plain'),
        ])
        ->assertStatus(422)
        ->assertJsonPath(
            'errors.file.0',
            'Format berkas belum didukung. Gunakan: JPG, JPEG, PNG, WEBP.',
        );
});

test('berkas melebihi batas ukuran ditolak', function () {
    // Batas galeri 5 MB.
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'gallery',
            'file' => UploadedFile::fake()->image('besar.jpg', 900, 900)->size(6 * 1024),
        ])
        ->assertStatus(422)
        ->assertJsonPath(
            'errors.file.0',
            'Ukuran berkas melebihi batas 5 MB. Kompres berkas terlebih dahulu.',
        );
});

test('gambar di bawah dimensi minimal ditolak', function () {
    // Sampul produk minimal 1200 x 900.
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'product_cover',
            'file' => UploadedFile::fake()->image('kecil.jpg', 600, 400),
        ])
        ->assertStatus(422)
        ->assertJsonPath(
            'errors.file.0',
            'Resolusi gambar terlalu kecil. Minimal 1200 × 900 agar tidak pecah saat ditampilkan.',
        );
});

test('jenis unggahan yang tidak dikenal ditolak', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'entah_apa',
            'file' => UploadedFile::fake()->image('foto.jpg', 900, 900),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('kind');
});

test('dokumen pdf diterima', function () {
    $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'document',
            'file' => UploadedFile::fake()->create('coa.pdf', 500, 'application/pdf'),
        ])
        ->assertCreated();
});

test('berkas sementara dapat dihapus lewat tokennya', function () {
    $token = $this->actingAs($this->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'gallery',
            'file' => UploadedFile::fake()->image('foto.jpg', 900, 900),
        ])
        ->json('token');

    $this->actingAs($this->user)
        ->deleteJson(route('admin.uploads.temp.destroy', $token))
        ->assertNoContent();

    expect(Storage::disk('local')->exists('tmp-uploads/'.$token))->toBeFalse();
});

test('token yang bukan ULID tidak bisa menembus direktori', function () {
    Storage::disk('local')->put('tmp-uploads/penting.txt', 'jangan hapus');

    $this->actingAs($this->user)
        ->deleteJson(route('admin.uploads.temp.destroy', '..'))
        ->assertNoContent();

    Storage::disk('local')->assertExists('tmp-uploads/penting.txt');
});
