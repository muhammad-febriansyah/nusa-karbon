<?php

declare(strict_types=1);

use App\Enums\UploadKind;
use App\Services\MediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->media = app(MediaUploadService::class);
});

test('nama berkas disanitasi dan diberi hash', function () {
    $file = UploadedFile::fake()->image('Foto Produk (Baru)#1.jpg', 900, 900);

    $name = $this->media->fileName($file);

    expect($name)->toMatch('/^foto-produk-baru1-[a-z0-9]{8}\.jpg$/');
});

test('nama berkas non-ASCII tetap menghasilkan nama yang aman', function () {
    $file = UploadedFile::fake()->image('Кömür Ürünü.png', 900, 900);

    $name = $this->media->fileName($file);

    expect($name)->toMatch('/^[a-z0-9-]+-[a-z0-9]{8}\.png$/');
});

test('dua berkas dengan nama sama tidak saling menimpa', function () {
    $first = $this->media->store(
        UploadedFile::fake()->image('sama.jpg', 900, 900),
        UploadKind::Gallery,
        'galeri',
    );

    $second = $this->media->store(
        UploadedFile::fake()->image('sama.jpg', 900, 900),
        UploadKind::Gallery,
        'galeri',
    );

    expect($first)->not->toBe($second);
    Storage::disk('public')->assertExists($first);
    Storage::disk('public')->assertExists($second);
});

test('mengganti berkas menghapus yang lama', function () {
    $old = $this->media->store(
        UploadedFile::fake()->image('lama.jpg', 900, 900),
        UploadKind::Gallery,
        'galeri',
    );

    $new = $this->media->replace(
        $old,
        UploadedFile::fake()->image('baru.jpg', 900, 900),
        UploadKind::Gallery,
        'galeri',
    );

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($new);
});

test('svg logo disimpan dalam keadaan tersanitasi', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><script>alert(1)</script><path d="M0 0"/></svg>';

    $path = $this->media->store(
        UploadedFile::fake()->createWithContent('logo.svg', $svg),
        UploadKind::Logo,
        'logo',
    );

    $stored = Storage::disk('public')->get($path);

    expect($stored)->not->toContain('script')
        ->not->toContain('alert')
        ->and($stored)->toContain('<path');
});

test('svg ditolak untuk jenis selain logo', function () {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0"/></svg>';

    expect(fn () => $this->media->store(
        UploadedFile::fake()->createWithContent('gambar.svg', $svg),
        UploadKind::Gallery,
        'galeri',
    ))->toThrow(RuntimeException::class, 'SVG hanya diterima untuk logo.');
});

test('svg rusak ditolak', function () {
    expect(fn () => $this->media->store(
        UploadedFile::fake()->createWithContent('rusak.svg', 'bukan svg'),
        UploadKind::Logo,
        'logo',
    ))->toThrow(RuntimeException::class, 'Berkas SVG tidak valid.');
});

test('menghapus path kosong tidak menimbulkan error', function () {
    expect(fn () => $this->media->delete(null))->not->toThrow(Exception::class);
    expect(fn () => $this->media->delete(''))->not->toThrow(Exception::class);
});
