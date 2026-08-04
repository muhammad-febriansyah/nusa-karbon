<?php

declare(strict_types=1);

use App\Models\ContainerType;
use App\Models\User;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use Database\Seeders\ContainerTypeSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses tipe kontainer', function () {
    $this->get(route('admin.container-types.index'))
        ->assertRedirect(route('login'));
});

test('halaman index tampil dengan daftar kontainer', function () {
    ContainerType::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.container-types.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/container-types/index')
            ->has('containerTypes.data', 3)
            ->has('containerTypes.meta')
            ->has('filters'),
        );
});

test('tipe kontainer baru dapat dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => '20FT_DV',
            'name' => ['id' => '20FT Dry Van'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
            'internal_length_m' => 5.90,
            'internal_width_m' => 2.35,
            'internal_height_m' => 2.39,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.container-types.index'))
        ->assertSessionHasNoErrors();

    $containerType = ContainerType::query()->first();

    expect($containerType)->not->toBeNull()
        ->and($containerType->code)->toBe('20FT_DV')
        ->and($containerType->getTranslation('name', 'id'))->toBe('20FT Dry Van')
        ->and($containerType->max_payload_kg)->toBe(18000.0)
        ->and($containerType->max_cbm)->toBe(28.0)
        ->and($containerType->is_active)->toBeTrue();
});

test('kode disimpan dalam huruf kapital', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => ' 40ft_hc ',
            'name' => ['id' => '40FT High Cube'],
            'max_payload_kg' => 26000,
            'max_cbm' => 68,
        ])
        ->assertSessionHasNoErrors();

    expect(ContainerType::query()->first()->code)->toBe('40FT_HC');
});

test('kode kontainer wajib unik', function () {
    ContainerType::factory()->create(['code' => '20FT_DV']);

    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => '20FT_DV',
            'name' => ['id' => 'Duplikat'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
        ])
        ->assertSessionHasErrors([
            'code' => 'Kode kontainer ini sudah dipakai. Gunakan yang lain.',
        ]);
});

test('kode kontainer sendiri tidak dianggap duplikat saat diubah', function () {
    $containerType = ContainerType::factory()->create(['code' => '20FT_DV']);

    $this->actingAs($this->user)
        ->put(route('admin.container-types.update', $containerType), [
            'code' => '20FT_DV',
            'name' => ['id' => 'Nama Baru'],
            'max_payload_kg' => 18500,
            'max_cbm' => 29,
        ])
        ->assertSessionHasNoErrors();

    expect($containerType->refresh()->max_payload_kg)->toBe(18500.0);
});

test('kode dengan karakter tidak sah ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => '20FT DV!',
            'name' => ['id' => '20FT Dry Van'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
        ])
        ->assertSessionHasErrors([
            'code' => 'Kode kontainer hanya boleh berisi huruf kapital, angka, garis bawah, dan tanda hubung.',
        ]);
});

test('nama kontainer wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-01',
            'name' => ['id' => ''],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
        ])
        ->assertSessionHasErrors([
            'name.id' => 'Nama kontainer wajib diisi.',
        ]);
});

test('kapasitas berat dan volume wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-02',
            'name' => ['id' => 'Tanpa Kapasitas'],
        ])
        ->assertSessionHasErrors([
            'max_payload_kg' => 'Kapasitas berat wajib diisi.',
            'max_cbm' => 'Kapasitas volume wajib diisi.',
        ]);
});

/*
 * Nol akan membuat Container Calculator mengembalikan 0 karton, jadi seluruh
 * nilai numerik harus lebih besar dari nol.
 */
test('nilai numerik nol ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-03',
            'name' => ['id' => 'Kosong'],
            'max_payload_kg' => 0,
            'max_cbm' => 0,
            'internal_length_m' => 0,
            'internal_width_m' => 0,
            'internal_height_m' => 0,
        ])
        ->assertSessionHasErrors([
            'max_payload_kg' => 'Kapasitas berat harus lebih dari 0.',
            'max_cbm' => 'Kapasitas volume harus lebih dari 0.',
            'internal_length_m' => 'Panjang internal harus lebih dari 0.',
            'internal_width_m' => 'Lebar internal harus lebih dari 0.',
            'internal_height_m' => 'Tinggi internal harus lebih dari 0.',
        ]);
});

test('nilai numerik negatif ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-04',
            'name' => ['id' => 'Negatif'],
            'max_payload_kg' => -100,
            'max_cbm' => -1,
        ])
        ->assertSessionHasErrors(['max_payload_kg', 'max_cbm']);
});

test('dimensi internal boleh dikosongkan', function () {
    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-05',
            'name' => ['id' => 'Tanpa Dimensi'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
        ])
        ->assertSessionHasNoErrors();

    $containerType = ContainerType::query()->first();

    expect($containerType->internal_length_m)->toBeNull()
        ->and($containerType->internalVolumeCbm())->toBeNull();
});

test('volume internal dihitung dari ketiga dimensi', function () {
    $containerType = ContainerType::factory()->create([
        'internal_length_m' => 5.90,
        'internal_width_m' => 2.35,
        'internal_height_m' => 2.39,
    ]);

    // 5.90 × 2.35 × 2.39 = 33.14 (dibulatkan 2 desimal)
    expect($containerType->internalVolumeCbm())->toBe(33.14);
});

test('urutan tampil bertambah otomatis untuk kontainer baru', function () {
    ContainerType::factory()->create(['sort_order' => 4]);

    $this->actingAs($this->user)
        ->post(route('admin.container-types.store'), [
            'code' => 'TEST-06',
            'name' => ['id' => 'Berikutnya'],
            'max_payload_kg' => 18000,
            'max_cbm' => 28,
        ])
        ->assertSessionHasNoErrors();

    expect(ContainerType::query()->where('code', 'TEST-06')->first()->sort_order)
        ->toBe(5);
});

test('halaman ubah mengirim data kontainer tanpa pembungkus data', function () {
    $containerType = ContainerType::factory()->create();

    $this->actingAs($this->user)
        ->get(route('admin.container-types.edit', $containerType))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/container-types/edit')
            ->where('containerType.code', $containerType->code)
            ->where('containerType.max_payload_kg', 18000)
            ->missing('containerType.data'),
        );
});

test('tipe kontainer dapat diperbarui', function () {
    $containerType = ContainerType::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.container-types.update', $containerType), [
            'code' => 'UPDATED-01',
            'name' => ['id' => 'Nama Diperbarui'],
            'max_payload_kg' => 20000,
            'max_cbm' => 30,
            'is_active' => false,
        ])
        ->assertSessionHasNoErrors();

    $containerType->refresh();

    expect($containerType->code)->toBe('UPDATED-01')
        ->and($containerType->getTranslation('name', 'id'))->toBe('Nama Diperbarui')
        ->and($containerType->max_payload_kg)->toBe(20000.0)
        ->and($containerType->is_active)->toBeFalse();
});

test('tipe kontainer dihapus secara soft delete', function () {
    $containerType = ContainerType::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.container-types.destroy', $containerType))
        ->assertSessionHasNoErrors();

    expect(ContainerType::query()->count())->toBe(0)
        ->and(ContainerType::withTrashed()->count())->toBe(1);
});

test('daftar dapat difilter berdasarkan status', function () {
    ContainerType::factory()->count(2)->create();
    ContainerType::factory()->inactive()->create();

    $this->actingAs($this->user)
        ->get(route('admin.container-types.index', ['is_active' => '0']))
        ->assertInertia(fn (Assert $page) => $page->has('containerTypes.data', 1));
});

test('pencarian menyasar kode dan nama', function () {
    ContainerType::factory()->create(['code' => '20FT_DV']);
    ContainerType::factory()->highCube()->create(['code' => '40FT_HC']);

    $this->actingAs($this->user)
        ->get(route('admin.container-types.index', ['search' => '40FT']))
        ->assertInertia(fn (Assert $page) => $page->has('containerTypes.data', 1));

    $this->actingAs($this->user)
        ->get(route('admin.container-types.index', ['search' => 'High Cube']))
        ->assertInertia(fn (Assert $page) => $page->has('containerTypes.data', 1));
});

test('kolom sort di luar daftar putih diabaikan', function () {
    ContainerType::factory()->count(2)->create();

    $this->actingAs($this->user)
        ->get(route('admin.container-types.index', ['sort_by' => 'password']))
        ->assertOk();
});

test('seeder membuat 20FT Dry Van dan 40FT High Cube', function () {
    $this->seed(ContainerTypeSeeder::class);

    $twentyFoot = ContainerType::query()->where('code', '20FT_DV')->first();
    $fortyFoot = ContainerType::query()->where('code', '40FT_HC')->first();

    expect($twentyFoot->max_payload_kg)->toBe(18000.0)
        ->and($twentyFoot->max_cbm)->toBe(28.0)
        ->and($twentyFoot->sort_order)->toBe(0)
        ->and($fortyFoot->max_payload_kg)->toBe(26000.0)
        ->and($fortyFoot->max_cbm)->toBe(68.0)
        ->and($fortyFoot->sort_order)->toBe(1);
});

test('seeder dapat dijalankan berulang tanpa menduplikasi', function () {
    $this->seed(ContainerTypeSeeder::class);
    $this->seed(ContainerTypeSeeder::class);

    expect(ContainerType::query()->count())->toBe(2);
});

test('hanya kontainer aktif yang diambil untuk kalkulator', function () {
    ContainerType::factory()->create(['sort_order' => 1]);
    ContainerType::factory()->inactive()->create(['sort_order' => 0]);

    $active = app(ContainerTypeRepositoryInterface::class)
        ->activeOrdered();

    expect($active)->toHaveCount(1);
});
