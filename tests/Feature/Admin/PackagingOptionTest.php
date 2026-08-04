<?php

declare(strict_types=1);

use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use Database\Seeders\PackagingOptionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->user = User::factory()->create();
});

/**
 * Mengunggah gambar ke endpoint sementara dan mengembalikan tokennya, persis
 * seperti yang dilakukan `ImageUploader` sebelum form disimpan.
 */
function uploadPackagingImageToken(string $name = 'kemasan.jpg'): string
{
    return test()->actingAs(test()->user)
        ->postJson(route('admin.uploads.temp.store'), [
            'kind' => 'gallery',
            'file' => UploadedFile::fake()->image($name, 900, 900),
        ])
        ->assertCreated()
        ->json('token');
}

test('tamu tidak bisa mengakses opsi kemasan', function () {
    $this->get(route('admin.packaging-options.index'))
        ->assertRedirect(route('login'));
});

test('halaman index tampil dengan daftar kemasan', function () {
    PackagingOption::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.packaging-options.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/packaging-options/index')
            ->has('packagingOptions.data', 3)
            ->has('filters'),
        );
});

/*
 * Daftar penuh tanpa pagination hanya dikirim saat mode atur urutan dibuka,
 * jadi muatan halaman biasa tidak ikut membawanya.
 */
test('daftar berurutan tidak ikut di muatan halaman biasa', function () {
    PackagingOption::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->get(route('admin.packaging-options.index'))
        ->assertInertia(fn (Assert $page) => $page->missing('orderedOptions'));
});

test('daftar berurutan mengikuti sort order', function () {
    $second = PackagingOption::factory()->create(['sort_order' => 1]);
    $first = PackagingOption::factory()->create(['sort_order' => 0]);
    $inactive = PackagingOption::factory()->inactive()->create(['sort_order' => 2]);

    $options = app(PackagingOptionRepositoryInterface::class);

    expect($options->ordered()->pluck('id')->all())
        ->toBe([$first->id, $second->id, $inactive->id])
        // Mode atur urutan memuat semuanya, multi-select produk hanya yang aktif.
        ->and($options->activeOrdered()->pluck('id')->all())
        ->toBe([$first->id, $second->id]);
});

test('kemasan baru dapat dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'description' => ['id' => 'Kotak karton isi 1 kg.'],
            'net_weight_kg' => 1,
            'is_active' => true,
        ])
        ->assertSessionHasNoErrors();

    $option = PackagingOption::query()->first();

    expect($option)->not->toBeNull()
        ->and($option->getTranslation('name', 'id'))->toBe('Inner Box 1 kg')
        ->and($option->net_weight_kg)->toBe(1.0)
        ->and($option->image_path)->toBeNull()
        ->and($option->is_active)->toBeTrue();
});

test('gambar kemasan tersimpan dari token unggahan', function () {
    $token = uploadPackagingImageToken();

    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => 'Dengan Gambar'],
            'image_token' => $token,
        ])
        ->assertSessionHasNoErrors();

    $option = PackagingOption::query()->first();

    expect($option->image_path)->toStartWith('packaging-options/');
    Storage::disk('public')->assertExists($option->image_path);
    expect(Storage::disk('local')->exists('tmp-uploads/'.$token))->toBeFalse();
});

test('mengganti gambar menghapus gambar lama', function () {
    $option = PackagingOption::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'image_token' => uploadPackagingImageToken('lama.jpg'),
        ])
        ->assertSessionHasNoErrors();

    $oldPath = $option->refresh()->image_path;

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'image_token' => uploadPackagingImageToken('baru.jpg'),
        ])
        ->assertSessionHasNoErrors();

    $option->refresh();

    expect($option->image_path)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($option->image_path);
});

test('gambar dapat dihapus tanpa mengunggah pengganti', function () {
    $option = PackagingOption::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'image_token' => uploadPackagingImageToken(),
        ]);

    $path = $option->refresh()->image_path;

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'remove_image' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($option->refresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('menyimpan tanpa token gambar tidak menghapus gambar lama', function () {
    $option = PackagingOption::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Inner Box 1 kg'],
            'image_token' => uploadPackagingImageToken(),
        ]);

    $path = $option->refresh()->image_path;

    $this->actingAs($this->user)
        ->put(route('admin.packaging-options.update', $option), [
            'name' => ['id' => 'Nama Diubah'],
        ])
        ->assertSessionHasNoErrors();

    expect($option->refresh()->image_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('nama kemasan wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => ''],
        ])
        ->assertSessionHasErrors([
            'name.id' => 'Nama kemasan wajib diisi.',
        ]);
});

test('berat bersih nol ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => 'Berat Nol'],
            'net_weight_kg' => 0,
        ])
        ->assertSessionHasErrors([
            'net_weight_kg' => 'Berat bersih harus lebih dari 0.',
        ]);
});

test('berat bersih boleh dikosongkan untuk kemasan custom', function () {
    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => 'Kemasan Custom Buyer'],
        ])
        ->assertSessionHasNoErrors();

    expect(PackagingOption::query()->first()->net_weight_kg)->toBeNull();
});

test('urutan tampil bertambah otomatis untuk kemasan baru', function () {
    PackagingOption::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.store'), [
            'name' => ['id' => 'Berikutnya'],
        ])
        ->assertSessionHasNoErrors();

    expect(PackagingOption::query()->orderByDesc('id')->first()->sort_order)
        ->toBe(3);
});

test('urutan dapat disimpan ulang lewat drag-and-drop', function () {
    $first = PackagingOption::factory()->create(['sort_order' => 0]);
    $second = PackagingOption::factory()->create(['sort_order' => 1]);
    $third = PackagingOption::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->user)
        ->post(route('admin.packaging-options.reorder'), [
            'ids' => [$third->id, $first->id, $second->id],
        ])
        ->assertSessionHasNoErrors();

    expect(PackagingOption::query()->pluck('sort_order', 'id')->all())
        ->toBe([$first->id => 1, $second->id => 2, $third->id => 0]);
});

test('kemasan dihapus secara soft delete', function () {
    $option = PackagingOption::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.packaging-options.destroy', $option))
        ->assertSessionHasNoErrors();

    expect(PackagingOption::query()->count())->toBe(0)
        ->and(PackagingOption::withTrashed()->count())->toBe(1);
});

test('seeder membuat empat opsi kemasan dan idempoten', function () {
    $this->seed(PackagingOptionSeeder::class);
    $this->seed(PackagingOptionSeeder::class);

    expect(PackagingOption::query()->count())->toBe(4)
        ->and(PackagingOption::query()->orderBy('sort_order')->first()->getTranslation('name', 'id'))
        ->toBe('Inner Box 1 kg');
});

test('kemasan ikut tersimpan saat produk dibuat', function () {
    $options = PackagingOption::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'PKG-01',
            'name' => ['id' => 'Produk Kemasan'],
            'slug' => ['id' => 'produk-kemasan'],
            'packaging_option_ids' => $options->pluck('id')->all(),
        ])
        ->assertSessionHasNoErrors();

    expect(Product::query()->first()->packagingOptions)->toHaveCount(2);
});

test('sinkronisasi pivot mengganti pilihan lama', function () {
    $product = Product::factory()->create();
    $old = PackagingOption::factory()->count(2)->create();
    $new = PackagingOption::factory()->create();

    $product->packagingOptions()->sync($old->pluck('id')->all());

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
            'packaging_option_ids' => [$new->id],
        ])
        ->assertSessionHasNoErrors();

    expect($product->packagingOptions()->pluck('packaging_options.id')->all())
        ->toBe([$new->id]);
});

test('memperbarui produk tanpa mengirim kemasan tidak menghapus pilihannya', function () {
    $product = Product::factory()->create();
    $options = PackagingOption::factory()->count(2)->create();
    $product->packagingOptions()->sync($options->pluck('id')->all());

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
        ])
        ->assertSessionHasNoErrors();

    expect($product->packagingOptions()->count())->toBe(2);
});

test('mengirim larik kosong menghapus seluruh pilihan kemasan', function () {
    $product = Product::factory()->create();
    $options = PackagingOption::factory()->count(2)->create();
    $product->packagingOptions()->sync($options->pluck('id')->all());

    $this->actingAs($this->user)
        ->put(route('admin.products.update', $product), [
            'code' => $product->code,
            'name' => ['id' => 'Nama Baru'],
            'slug' => ['id' => 'nama-baru'],
            'packaging_option_ids' => [],
        ])
        ->assertSessionHasNoErrors();

    expect($product->packagingOptions()->count())->toBe(0);
});

test('id kemasan yang tidak ada ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.products.store'), [
            'code' => 'PKG-02',
            'name' => ['id' => 'Produk'],
            'slug' => ['id' => 'produk'],
            'packaging_option_ids' => [99999],
        ])
        ->assertSessionHasErrors('packaging_option_ids.0');
});

test('menghapus produk permanen membersihkan pivot', function () {
    $product = Product::factory()->create();
    $option = PackagingOption::factory()->create();
    $product->packagingOptions()->sync([$option->id]);

    $product->forceDelete();

    expect($option->products()->count())->toBe(0)
        ->and(PackagingOption::query()->count())->toBe(1);
});

test('hanya kemasan aktif yang ditawarkan di form produk', function () {
    PackagingOption::factory()->count(2)->create();
    PackagingOption::factory()->inactive()->create();

    $this->actingAs($this->user)
        ->get(route('admin.products.create'))
        ->assertInertia(fn (Assert $page) => $page->has('packagingOptions', 2));
});

test('kemasan terpilih dikirim ke halaman ubah produk', function () {
    $product = Product::factory()->create();
    $option = PackagingOption::factory()->create();
    $product->packagingOptions()->sync([$option->id]);

    $this->actingAs($this->user)
        ->get(route('admin.products.edit', $product))
        ->assertInertia(fn (Assert $page) => $page
            ->has('product.packaging_option_ids', 1)
            ->where('product.packaging_option_ids.0', $option->id),
        );
});
