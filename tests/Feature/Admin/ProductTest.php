<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses produk', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['admin.products.index', 'admin.products.create']);

test('halaman index menampilkan produk beserta kategorinya', function () {
    Product::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/products/index')
            ->has('products.data', 3)
            ->has('products.data.0.category')
            ->has('categories')
            ->has('filters'),
        );
});

test('produk baru dapat dibuat', function () {
    $category = ProductCategory::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'product_category_id' => $category->id,
            'code' => 'SHISHA-PREM-26',
            'name' => ['id' => 'Shisha Premium 26mm'],
            'slug' => ['id' => 'shisha-premium-26mm'],
            'shape' => 'Cube',
            'size_mm' => '26 x 26 x 26',
            'master_carton_weight_kg' => 20,
            'master_carton_cbm' => 0.043,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHasNoErrors();

    $product = Product::query()->first();

    expect($product)->not->toBeNull()
        ->and($product->code)->toBe('SHISHA-PREM-26')
        ->and($product->getTranslation('name', 'id'))->toBe('Shisha Premium 26mm')
        ->and($product->product_category_id)->toBe($category->id)
        ->and($product->isCalculable())->toBeTrue();
});

test('kode produk dinormalkan menjadi huruf kapital', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => '  shisha-prem-26  ',
            'name' => ['id' => 'Shisha Premium'],
            'slug' => ['id' => 'shisha-premium'],
        ])
        ->assertSessionHasNoErrors();

    expect(Product::query()->first()->code)->toBe('SHISHA-PREM-26');
});

test('kode produk harus unik', function () {
    Product::factory()->create(['code' => 'SUDAH-ADA']);

    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'SUDAH-ADA',
            'name' => ['id' => 'Produk Baru'],
            'slug' => ['id' => 'produk-baru'],
        ])
        ->assertSessionHasErrors([
            'code' => 'Kode produk ini sudah dipakai produk lain. Gunakan yang lain.',
        ]);
});

test('slug harus unik per locale', function () {
    Product::factory()->create(['slug' => ['id' => 'sudah-dipakai']]);

    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'KODE-LAIN',
            'name' => ['id' => 'Produk Baru'],
            'slug' => ['id' => 'sudah-dipakai'],
        ])
        ->assertSessionHasErrors([
            'slug.id' => 'Slug ini sudah dipakai produk lain. Gunakan yang lain.',
        ]);
});

test('nama produk wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'TANPA-NAMA',
            'name' => ['id' => ''],
            'slug' => ['id' => 'tanpa-nama'],
        ])
        ->assertSessionHasErrors(['name.id' => 'Nama produk wajib diisi.']);
});

test('slug dibuat otomatis dari nama', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'AUTO-SLUG',
            'name' => ['id' => 'Briket Hexagonal 50mm'],
            'slug' => ['id' => ''],
        ])
        ->assertSessionHasNoErrors();

    expect(Product::query()->first()->getTranslation('slug', 'id'))
        ->toBe('briket-hexagonal-50mm');
});

test('berat master carton nol ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'BERAT-NOL',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'master_carton_weight_kg' => 0,
        ])
        ->assertSessionHasErrors([
            'master_carton_weight_kg' => 'Berat master carton harus lebih dari 0.',
        ]);
});

test('produk dapat diubah', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Diubah'],
            'slug' => ['id' => 'nama-diubah'],
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors();

    $product->refresh();

    expect($product->getTranslation('name', 'id'))->toBe('Nama Diubah')
        ->and($product->is_active)->toBeFalse();
});

test('kode produk sendiri tidak dianggap duplikat saat diubah', function () {
    $product = Product::factory()->create(['code' => 'TETAP-SAMA']);

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => 'TETAP-SAMA',
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
        ])
        ->assertSessionHasNoErrors();
});

test('produk dihapus secara soft delete', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.products.destroy', $product))
        ->assertSessionHasNoErrors();

    expect(Product::query()->whereKey($product->id)->exists())->toBeFalse()
        ->and(Product::withTrashed()->whereKey($product->id)->first()?->deleted_at)
        ->not->toBeNull();
});

test('duplikat membuat salinan nonaktif dengan kode dan slug unik', function () {
    $product = Product::factory()->calculable()->create([
        'code' => 'ASLI',
        'name' => ['id' => 'Produk Asli'],
        'slug' => ['id' => 'produk-asli'],
        'is_active' => true,
        'is_featured' => true,
    ]);

    $this->actingAs($this->user)
        ->post(route('admin.products.duplicate', $product))
        ->assertSessionHasNoErrors();

    $copy = Product::query()->where('code', '!=', 'ASLI')->first();

    expect($copy)->not->toBeNull()
        ->and($copy->code)->toBe('ASLI-COPY')
        ->and($copy->getTranslation('slug', 'id'))->toBe('produk-asli-copy')
        ->and($copy->getTranslation('name', 'id'))->toBe('Produk Asli (Salinan)')
        ->and($copy->is_active)->toBeFalse()
        ->and($copy->is_featured)->toBeFalse()
        ->and($copy->master_carton_weight_kg)->toBe(20.0);
});

test('duplikat kedua tetap menghasilkan kode unik', function () {
    $product = Product::factory()->create(['code' => 'ASLI']);

    $this->actingAs($this->user)->post(route('admin.products.duplicate', $product));
    $this->actingAs($this->user)->post(route('admin.products.duplicate', $product));

    expect(Product::query()->pluck('code')->sort()->values()->all())
        ->toBe(['ASLI', 'ASLI-COPY', 'ASLI-COPY2']);
});

test('scope calculable hanya memuat produk berdata lengkap dan aktif', function () {
    Product::factory()->calculable()->create();
    Product::factory()->create();
    Product::factory()->calculable()->inactive()->create();

    expect(Product::query()->calculable()->count())->toBe(1);
});

test('produk tanpa data carton ditandai belum calculable', function () {
    Product::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.is_calculable', false),
        );
});

test('filter master carton belum lengkap menyaring produk', function () {
    Product::factory()->calculable()->count(2)->create();
    Product::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.index', ['missing_carton' => '1']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));
});

test('filter kategori menyaring produk', function () {
    $category = ProductCategory::factory()->create();
    Product::factory()->create(['product_category_id' => $category->id]);
    Product::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.index', ['product_category_id' => $category->id]))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));
});

test('pencarian menyasar kode dan nama', function () {
    Product::factory()->create([
        'code' => 'SHISHA-01',
        'name' => ['id' => 'Shisha Premium'],
        'slug' => ['id' => 'shisha-premium'],
    ]);
    Product::factory()->create([
        'code' => 'BBQ-01',
        'name' => ['id' => 'BBQ Hexagonal'],
        'slug' => ['id' => 'bbq-hexagonal'],
    ]);

    $this->actingAs($this->user)
        ->get(route('admin.products.index', ['search' => 'SHISHA-01']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));

    $this->actingAs($this->user)
        ->get(route('admin.products.index', ['search' => 'Hexagonal']))
        ->assertInertia(fn (Assert $page) => $page->has('products.data', 1));
});

test('aksi massal mengaktifkan produk terpilih', function () {
    $products = Product::factory()->inactive()->count(3)->create();

    $this->actingAs($this->user)
        ->post(route('admin.products.bulk'), [
            'action' => 'activate',
            'ids' => $products->pluck('id')->all(),
        ])
        ->assertSessionHasNoErrors();

    expect(Product::query()->where('is_active', true)->count())->toBe(3);
});

test('aksi massal mengarsipkan produk terpilih', function () {
    $products = Product::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->post(route('admin.products.bulk'), [
            'action' => 'archive',
            'ids' => $products->pluck('id')->all(),
        ])
        ->assertSessionHasNoErrors();

    expect(Product::query()->count())->toBe(0)
        ->and(Product::withTrashed()->count())->toBe(2);
});

test('aksi massal yang tidak dikenal ditolak', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->user)
        ->post(route('admin.products.bulk'), [
            'action' => 'hapus-permanen',
            'ids' => [$product->id],
        ])
        ->assertSessionHasErrors('action');
});

test('kategori yang masih dipakai produk tidak bisa dihapus', function () {
    $category = ProductCategory::factory()->create();
    Product::factory()->create(['product_category_id' => $category->id]);

    $this->actingAs($this->user)
        ->delete(route('admin.product-categories.destroy', $category));

    expect(ProductCategory::query()->whereKey($category->id)->exists())->toBeTrue();
});

test('kategori tanpa produk tetap bisa dihapus', function () {
    $category = ProductCategory::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.product-categories.destroy', $category))
        ->assertSessionHasNoErrors();

    expect(ProductCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

test('menghapus kategori tidak ikut menghapus produknya', function () {
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);

    // Dihapus langsung lewat model untuk melewati penjagaan di Action.
    $category->delete();

    expect($product->refresh()->product_category_id)->toBe($category->id);
});

test('pengguna yang membuat produk tercatat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'BLAME-01',
            'name' => ['id' => 'Produk Blame'],
            'slug' => ['id' => 'produk-blame'],
        ]);

    expect(Product::query()->first()->created_by)->toBe($this->user->id);
});
