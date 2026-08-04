<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductDocument;
use App\Models\SiteSetting;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\MediaLibraryService;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->create();
});

/**
 * Membuat berkas gambar 1×1 piksel supaya dimensinya terbaca.
 */
function fakeImage(string $path): void
{
    $image = imagecreatetruecolor(4, 2);

    ob_start();
    imagepng($image);
    $binary = (string) ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put($path, $binary);
}

test('tamu tidak bisa mengakses media library', function () {
    $this->get(route('admin.media.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat berkas beserta ringkasannya', function () {
    fakeImage('certificates/logos/iso.png');
    Storage::disk('public')->put('posts/covers/panduan.pdf', 'x');

    Certificate::factory()->create(['logo_path' => 'certificates/logos/iso.png']);

    $this->actingAs($this->user)
        ->get(route('admin.media.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/media/index')
            ->has('files', 2)
            ->where('summary.total', 2)
            ->where('summary.orphans', 1),
        );
});

test('berkas sementara, hasil konversi, dan berkas sistem tidak ikut ditampilkan', function () {
    Storage::disk('public')->put('tmp-uploads/abc/berkas.png', 'x');
    Storage::disk('public')->put('1/conversions/produk-thumb.webp', 'x');
    // `.gitignore` bawaan Laravel menjaga direktori storage tetap ada; ikut
    // terbaca berarti ikut terhapus saat pembersihan massal.
    Storage::disk('public')->put('.gitignore', "*\n!.gitignore\n");
    Storage::disk('public')->put('galleries/.DS_Store', 'x');
    Storage::disk('public')->put('features/ikon.png', 'x');

    $files = app(MediaLibraryService::class)->inventory();

    expect($files)->toHaveCount(1)
        ->and($files[0]['path'])->toBe('features/ikon.png');
});

test('berkas sistem tidak ikut terhapus saat pembersihan massal', function () {
    Storage::disk('public')->put('.gitignore', "*\n!.gitignore\n");
    Storage::disk('public')->put('posts/covers/yatim.png', 'x');

    $this->actingAs($this->user)
        ->delete(route('admin.media.orphans'))
        ->assertRedirect();

    expect(Storage::disk('public')->exists('.gitignore'))->toBeTrue()
        ->and(Storage::disk('public')->exists('posts/covers/yatim.png'))->toBeFalse();

    // Dikirim manual pun tetap ditolak, bukan hanya disembunyikan dari daftar.
    $this->actingAs($this->user)
        ->delete(route('admin.media.destroy'), ['paths' => ['.gitignore']])
        ->assertRedirect();

    expect(Storage::disk('public')->exists('.gitignore'))->toBeTrue();
});

test('pemakai berkas terdeteksi lintas modul termasuk pengaturan', function () {
    fakeImage('team-members/bayu.png');
    fakeImage('settings/logo.png');

    TeamMember::factory()->create([
        'name' => 'Bayu Pratama',
        'photo_path' => 'team-members/bayu.png',
    ]);

    SiteSetting::factory()->create([
        'key' => 'logo_primary',
        'label' => 'Logo',
        'value' => ['data' => 'settings/logo.png'],
    ]);

    $media = app(MediaLibraryService::class);

    expect($media->usagesFor('team-members/bayu.png'))->toBe([
        ['module' => 'Tim', 'label' => 'Bayu Pratama'],
    ])->and($media->usagesFor('settings/logo.png'))->toBe([
        ['module' => 'Pengaturan Situs', 'label' => 'Logo'],
    ]);
});

test('metadata berkas gambar memuat dimensi dan ukuran', function () {
    fakeImage('features/ikon.png');

    $file = app(MediaLibraryService::class)->inventory()[0];

    expect($file['type'])->toBe('image')
        ->and($file['dimensions'])->toBe(['width' => 4, 'height' => 2])
        ->and($file['size'])->toBeGreaterThan(0);
});

test('berkas yang masih dipakai tidak bisa dihapus', function () {
    fakeImage('certificates/logos/iso.png');
    Certificate::factory()->create(['logo_path' => 'certificates/logos/iso.png']);

    $this->actingAs($this->user)
        ->delete(route('admin.media.destroy'), [
            'paths' => ['certificates/logos/iso.png'],
        ])
        ->assertSessionHasErrors('media');

    expect(Storage::disk('public')->exists('certificates/logos/iso.png'))->toBeTrue();
});

test('berkas yatim dapat dihapus', function () {
    Storage::disk('public')->put('posts/covers/lama.png', 'x');

    $this->actingAs($this->user)
        ->delete(route('admin.media.destroy'), [
            'paths' => ['posts/covers/lama.png'],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Storage::disk('public')->exists('posts/covers/lama.png'))->toBeFalse();
});

test('pembersihan massal hanya menghapus berkas yatim', function () {
    fakeImage('posts/covers/dipakai.png');
    Storage::disk('public')->put('posts/covers/yatim-1.png', 'x');
    Storage::disk('public')->put('galleries/items/yatim-2.jpg', 'x');

    Post::factory()->create(['cover_path' => 'posts/covers/dipakai.png']);

    $this->actingAs($this->user)
        ->delete(route('admin.media.orphans'))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Storage::disk('public')->exists('posts/covers/dipakai.png'))->toBeTrue()
        ->and(Storage::disk('public')->exists('posts/covers/yatim-1.png'))->toBeFalse()
        ->and(Storage::disk('public')->exists('galleries/items/yatim-2.jpg'))->toBeFalse();
});

test('filter tipe dan status pemakaian menyaring daftar', function () {
    fakeImage('features/ikon.png');
    Storage::disk('public')->put('certificates/files/scan.pdf', 'x');

    $this->actingAs($this->user)
        ->get(route('admin.media.index', ['type' => 'image']))
        ->assertInertia(fn (Assert $page) => $page->has('files', 1));

    $this->actingAs($this->user)
        ->get(route('admin.media.index', ['usage' => 'used']))
        ->assertInertia(fn (Assert $page) => $page->has('files', 0));

    $this->actingAs($this->user)
        ->get(route('admin.media.index', ['search' => 'scan']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('files', 1)
            ->where('files.0.name', 'scan.pdf'),
        );
});

test('endpoint pemilih hanya mengembalikan gambar', function () {
    fakeImage('features/ikon.png');
    Storage::disk('public')->put('certificates/files/scan.pdf', 'x');

    $this->actingAs($this->user)
        ->getJson(route('admin.media.picker'))
        ->assertOk()
        ->assertJsonCount(1, 'files')
        ->assertJsonPath('files.0.name', 'ikon.png');
});

test('dokumen produk terdaftar sebagai pemakai berkas', function () {
    Storage::disk('public')->put('product-documents/coa.pdf', '%PDF-1.4 demo');

    $product = Product::factory()->create();

    ProductDocument::factory()->create([
        'product_id' => $product->id,
        'file_path' => 'product-documents/coa.pdf',
        'file_name' => 'coa.pdf',
    ]);

    // Tanpa entri di peta pemakaian, PDF dokumen terbaca sebagai berkas tanpa
    // pemakai dan ikut terhapus saat pembersihan massal.
    expect(app(MediaLibraryService::class)->usagesFor('product-documents/coa.pdf'))->toBe([
        ['module' => 'Dokumen Produk', 'label' => 'coa.pdf'],
    ]);
});
