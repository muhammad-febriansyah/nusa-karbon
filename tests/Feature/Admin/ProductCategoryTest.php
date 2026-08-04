<?php

declare(strict_types=1);

use App\Models\ProductCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses kategori produk', function () {
    $this->get(route('admin.product-categories.index'))
        ->assertRedirect(route('login'));
});

test('halaman index tampil dengan daftar kategori', function () {
    ProductCategory::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/product-categories/index')
            ->has('categories.data', 3)
            ->has('categories.meta')
            ->has('filters'),
        );
});

test('kategori baru dapat dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => 'Shisha Charcoal'],
            'slug' => ['id' => 'shisha-charcoal'],
            'description' => ['id' => 'Untuk shisha.'],
            'icon' => 'flame',
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    $category = ProductCategory::query()->first();

    expect($category)->not->toBeNull()
        ->and($category->getTranslation('name', 'id'))->toBe('Shisha Charcoal')
        ->and($category->getTranslation('slug', 'id'))->toBe('shisha-charcoal')
        ->and($category->icon)->toBe('flame')
        ->and($category->is_active)->toBeTrue();
});

test('slug dibuat otomatis dari nama bila dikosongkan', function () {
    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => 'BBQ Charcoal Premium'],
            'slug' => ['id' => ''],
        ])
        ->assertSessionHasNoErrors();

    expect(ProductCategory::query()->first()->getTranslation('slug', 'id'))
        ->toBe('bbq-charcoal-premium');
});

test('nama Bahasa Indonesia wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => ''],
            'slug' => ['id' => 'tanpa-nama'],
        ])
        ->assertSessionHasErrors([
            'name.id' => 'Nama kategori wajib diisi.',
        ]);
});

test('slug harus unik per locale', function () {
    ProductCategory::factory()->create([
        'slug' => ['id' => 'sudah-dipakai'],
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => 'Kategori Baru'],
            'slug' => ['id' => 'sudah-dipakai'],
        ])
        ->assertSessionHasErrors([
            'slug.id' => 'Slug ini sudah dipakai kategori lain. Gunakan yang lain.',
        ]);
});

test('slug berbeda tetap diterima', function () {
    ProductCategory::factory()->create([
        'slug' => ['id' => 'arang'],
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => 'Arang Batok'],
            'slug' => ['id' => 'arang-batok'],
        ])
        ->assertSessionHasNoErrors();

    expect(ProductCategory::query()->count())->toBe(2);
});

test('format slug tidak valid ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.product-categories.store'), [
            'name' => ['id' => 'Kategori'],
            'slug' => ['id' => 'Slug Dengan Spasi'],
        ])
        ->assertSessionHasErrors([
            'slug.id' => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
        ]);
});

test('kategori dapat diubah', function () {
    $category = ProductCategory::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.product-categories.update', $category), [
            'name' => ['id' => 'Nama Diubah'],
            'slug' => ['id' => 'nama-diubah'],
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors();

    $category->refresh();

    expect($category->getTranslation('name', 'id'))->toBe('Nama Diubah')
        ->and($category->is_active)->toBeFalse();
});

test('slug kategori sendiri tidak dianggap duplikat saat diubah', function () {
    $category = ProductCategory::factory()->create([
        'slug' => ['id' => 'tetap-sama'],
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.product-categories.update', $category), [
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'tetap-sama'],
        ])
        ->assertSessionHasNoErrors();
});

test('kategori dihapus secara soft delete', function () {
    $category = ProductCategory::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.product-categories.destroy', $category))
        ->assertSessionHasNoErrors();

    expect(ProductCategory::query()->whereKey($category->id)->exists())->toBeFalse()
        ->and(ProductCategory::withTrashed()->whereKey($category->id)->first()?->deleted_at)
        ->not->toBeNull();
});

test('urutan kategori dapat disimpan ulang', function () {
    $first = ProductCategory::factory()->create(['sort_order' => 0]);
    $second = ProductCategory::factory()->create(['sort_order' => 1]);
    $third = ProductCategory::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->user)
        ->post(route('admin.product-categories.reorder'), [
            'ids' => [$third->id, $first->id, $second->id],
        ])
        ->assertSessionHasNoErrors();

    expect($third->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1)
        ->and($second->refresh()->sort_order)->toBe(2);
});

test('pencarian menyaring berdasarkan nama Bahasa Indonesia', function () {
    ProductCategory::factory()->create([
        'name' => ['id' => 'Shisha Charcoal'],
        'slug' => ['id' => 'shisha-charcoal'],
    ]);
    ProductCategory::factory()->create([
        'name' => ['id' => 'BBQ Charcoal'],
        'slug' => ['id' => 'bbq-charcoal'],
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index', ['search' => 'Shisha']))
        ->assertInertia(fn (Assert $page) => $page->has('categories.data', 1));
});

test('filter status menyaring kategori nonaktif', function () {
    ProductCategory::factory()->count(2)->create();
    ProductCategory::factory()->inactive()->create();

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index', ['is_active' => '0']))
        ->assertInertia(fn (Assert $page) => $page->has('categories.data', 1));
});

test('kolom sort di luar daftar putih diabaikan', function () {
    ProductCategory::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index', ['sort_by' => 'id); drop table users;--']))
        ->assertOk();
});

/*
 * Kelengkapan konten dilaporkan per locale aktif. Untuk sekarang hanya Bahasa
 * Indonesia; saat locale lain dihidupkan di `config/locales.php`, kunci
 * tambahan muncul otomatis tanpa mengubah Resource.
 */
test('kelengkapan konten dilaporkan untuk locale aktif', function () {
    ProductCategory::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('categories.data.0.completeness.id', 'complete'),
        );
});

test('kategori tanpa nama ditandai kosong', function () {
    ProductCategory::factory()->create([
        'name' => [],
        'slug' => [],
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.product-categories.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('categories.data.0.completeness.id', 'empty'),
        );
});
