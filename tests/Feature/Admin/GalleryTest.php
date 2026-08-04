<?php

declare(strict_types=1);

use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses galeri', function () {
    $this->get(route('admin.galleries.index'))
        ->assertRedirect(route('login'));
});

test('halaman index menampilkan album beserta jumlah itemnya', function () {
    $gallery = Gallery::factory()->create();
    GalleryItem::factory()->count(3)->create(['gallery_id' => $gallery->id]);

    $this->actingAs($this->user)
        ->get(route('admin.galleries.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/galleries/index')
            ->has('items', 1)
            ->where('items.0.items_count', 3),
        );
});

test('album baru mengarahkan ke halaman kelola isi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.galleries.store'), [
            'title' => ['id' => 'Proses Stuffing'],
            'slug' => ['id' => 'proses-stuffing'],
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    $gallery = Gallery::query()->firstOrFail();

    expect($gallery->getTranslation('slug', 'id'))->toBe('proses-stuffing');

    $this->actingAs($this->user)
        ->post(route('admin.galleries.store'), [
            'title' => ['id' => 'Album Kedua'],
            'slug' => ['id' => 'album-kedua'],
        ])
        ->assertRedirect(route('admin.galleries.items.edit', Gallery::query()->latest('id')->first()));
});

test('slug diisi otomatis dari judul bila dikosongkan', function () {
    $this->actingAs($this->user)
        ->post(route('admin.galleries.store'), [
            'title' => ['id' => 'Quality Control Harian'],
            'slug' => ['id' => ''],
        ])
        ->assertSessionHasNoErrors();

    expect(Gallery::query()->first()->getTranslation('slug', 'id'))
        ->toBe('quality-control-harian');
});

test('slug album wajib unik', function () {
    Gallery::factory()->create(['slug' => ['id' => 'proses-stuffing']]);

    $this->actingAs($this->user)
        ->post(route('admin.galleries.store'), [
            'title' => ['id' => 'Album Lain'],
            'slug' => ['id' => 'proses-stuffing'],
        ])
        ->assertSessionHasErrors('slug.id');
});

test('album dihapus secara soft delete beserta itemnya tetap utuh', function () {
    $gallery = Gallery::factory()->create();
    GalleryItem::factory()->create(['gallery_id' => $gallery->id]);

    $this->actingAs($this->user)
        ->delete(route('admin.galleries.destroy', $gallery))
        ->assertRedirect();

    expect(Gallery::query()->count())->toBe(0)
        ->and(Gallery::withTrashed()->count())->toBe(1)
        ->and(GalleryItem::query()->count())->toBe(1);
});

test('halaman kelola isi memuat item album berurutan', function () {
    $gallery = Gallery::factory()->create();
    GalleryItem::factory()->create(['gallery_id' => $gallery->id, 'sort_order' => 1]);
    GalleryItem::factory()->video()->create(['gallery_id' => $gallery->id, 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->get(route('admin.galleries.items.edit', $gallery))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/galleries/items')
            ->has('gallery.items', 2)
            ->where('gallery.items.0.type', 'video')
            ->has('itemTypes', 2),
        );
});

test('menyimpan isi album memperbarui urutan dan caption', function () {
    $gallery = Gallery::factory()->create();
    $first = GalleryItem::factory()->create(['gallery_id' => $gallery->id, 'sort_order' => 0]);
    $second = GalleryItem::factory()->create(['gallery_id' => $gallery->id, 'sort_order' => 1]);

    $this->actingAs($this->user)
        ->put(route('admin.galleries.items.sync', $gallery), [
            'items' => [
                ['id' => $second->id, 'type' => 'image', 'caption' => ['id' => 'Kedua jadi pertama']],
                ['id' => $first->id, 'type' => 'image', 'caption' => ['id' => 'Pertama jadi kedua']],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($second->getTranslation('caption', 'id'))->toBe('Kedua jadi pertama')
        ->and($first->refresh()->sort_order)->toBe(1);
});

test('item yang dibuang dihapus beserta berkasnya', function () {
    Storage::fake('public');
    Storage::disk('public')->put('galleries/items/lama.jpg', 'x');

    $gallery = Gallery::factory()->create();
    $keep = GalleryItem::factory()->create(['gallery_id' => $gallery->id]);
    GalleryItem::factory()->create([
        'gallery_id' => $gallery->id,
        'file_path' => 'galleries/items/lama.jpg',
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.galleries.items.sync', $gallery), [
            'items' => [
                ['id' => $keep->id, 'type' => 'image'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(GalleryItem::query()->count())->toBe(1)
        ->and(Storage::disk('public')->exists('galleries/items/lama.jpg'))->toBeFalse();
});

test('item video menyimpan URL dan menolak alamat tidak valid', function () {
    $gallery = Gallery::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.galleries.items.sync', $gallery), [
            'items' => [
                ['type' => 'video', 'video_url' => 'https://youtu.be/abc123'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(GalleryItem::query()->first()->video_url)->toBe('https://youtu.be/abc123');

    $this->actingAs($this->user)
        ->put(route('admin.galleries.items.sync', $gallery), [
            'items' => [
                ['type' => 'video', 'video_url' => 'bukan-url'],
            ],
        ])
        ->assertSessionHasErrors('items.0.video_url');
});

test('item baru tanpa berkas maupun URL ditolak', function () {
    $gallery = Gallery::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.galleries.items.sync', $gallery), [
            'items' => [
                ['type' => 'image', 'caption' => ['id' => 'Tanpa berkas']],
            ],
        ])
        ->assertSessionHasErrors('items.0.token');

    expect(GalleryItem::query()->count())->toBe(0);
});

test('urutan album tersimpan', function () {
    $first = Gallery::factory()->create(['sort_order' => 0]);
    $second = Gallery::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->user)
        ->post(route('admin.galleries.reorder'), ['ids' => [$second->id, $first->id]])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($second->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1);
});
