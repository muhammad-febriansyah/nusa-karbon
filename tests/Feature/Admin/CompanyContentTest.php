<?php

declare(strict_types=1);

use App\Models\Feature;
use App\Models\ProductionStep;
use App\Models\QcProtocol;
use App\Models\ShippingPartner;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\CompanyContentSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * Keenam modul konten perusahaan memakai pola yang sama, jadi pengujiannya
 * dijalankan sebagai dataset alih-alih disalin lima kali.
 *
 * @return array<string, array{class-string<Model>, string, array<string, mixed>, string}>
 */
dataset('content modules', [
    'proses produksi' => [
        ProductionStep::class,
        'production-steps',
        ['title' => ['id' => 'Karbonisasi'], 'description' => ['id' => 'Batok dibakar dalam tungku tertutup.']],
        'image_path',
    ],
    'protokol QC' => [
        QcProtocol::class,
        'qc-protocols',
        ['title' => ['id' => 'Drop Test'], 'result_label' => ['id' => 'Breakage tolerance'], 'result_value' => '< 2.5%'],
        'image_path',
    ],
    'keunggulan' => [
        Feature::class,
        'features',
        ['title' => ['id' => 'Kualitas Konsisten'], 'icon' => 'shield-check'],
        'image_path',
    ],
    'mitra pelayaran' => [
        ShippingPartner::class,
        'shipping-partners',
        ['name' => 'Maersk', 'website_url' => 'https://www.maersk.com'],
        'logo_path',
    ],
    'anggota tim' => [
        TeamMember::class,
        'team-members',
        ['name' => 'Adi Wicaksono', 'position' => ['id' => 'Operations Manager']],
        'photo_path',
    ],
]);

test('tamu tidak bisa mengakses konten perusahaan', function (string $model, string $route) {
    $this->get(route("admin.{$route}.index"))
        ->assertRedirect(route('login'));
})->with('content modules');

test('halaman index tampil berurutan', function (string $model, string $route) {
    $model::factory()->create(['sort_order' => 2]);
    $model::factory()->create(['sort_order' => 0]);
    $model::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->user)
        ->get(route("admin.{$route}.index"))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component("admin/{$route}/index")
            ->has('items', 3)
            ->where('items.0.sort_order', 0)
            ->where('items.2.sort_order', 2),
        );
})->with('content modules');

test('data baru dibuat di urutan terakhir', function (string $model, string $route, array $payload) {
    $model::factory()->create(['sort_order' => 4]);

    $this->actingAs($this->user)
        ->post(route("admin.{$route}.store"), $payload + ['is_active' => true])
        ->assertRedirect(route("admin.{$route}.index"))
        ->assertSessionHasNoErrors();

    expect($model::query()->latest('id')->first()->sort_order)->toBe(5);
})->with('content modules');

test('data dapat diperbarui', function (string $model, string $route, array $payload) {
    $item = $model::factory()->create();

    $this->actingAs($this->user)
        ->put(route("admin.{$route}.update", $item), $payload + ['is_active' => false])
        ->assertRedirect(route("admin.{$route}.index"))
        ->assertSessionHasNoErrors();

    expect($item->refresh()->is_active)->toBeFalse();
})->with('content modules');

test('data dihapus secara soft delete', function (string $model, string $route) {
    $item = $model::factory()->create();

    $this->actingAs($this->user)
        ->delete(route("admin.{$route}.destroy", $item))
        ->assertRedirect();

    expect($model::query()->count())->toBe(0)
        ->and($model::withTrashed()->count())->toBe(1);
})->with('content modules');

test('urutan baru tersimpan', function (string $model, string $route) {
    $first = $model::factory()->create(['sort_order' => 0]);
    $second = $model::factory()->create(['sort_order' => 1]);
    $third = $model::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->user)
        ->post(route("admin.{$route}.reorder"), [
            'ids' => [$third->id, $first->id, $second->id],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($third->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1)
        ->and($second->refresh()->sort_order)->toBe(2);
})->with('content modules');

test('berkas lama dihapus saat dibuang dari form', function (
    string $model,
    string $route,
    array $payload,
    string $fileColumn,
) {
    Storage::fake('public');
    Storage::disk('public')->put('lama/berkas.jpg', 'x');

    $item = $model::factory()->create([$fileColumn => 'lama/berkas.jpg']);

    $this->actingAs($this->user)
        ->put(route("admin.{$route}.update", $item), $payload + [
            "remove_{$fileColumn}" => true,
        ])
        ->assertSessionHasNoErrors();

    expect($item->refresh()->{$fileColumn})->toBeNull()
        ->and(Storage::disk('public')->exists('lama/berkas.jpg'))->toBeFalse();
})->with('content modules');

test('judul wajib diisi', function (string $model, string $route, array $payload) {
    $required = array_key_exists('name', $payload) ? 'name' : 'title.id';

    $this->actingAs($this->user)
        ->post(route("admin.{$route}.store"), [])
        ->assertSessionHasErrors($required);

    expect($model::query()->count())->toBe(0);
})->with('content modules');

test('alamat situs mitra harus URL lengkap', function () {
    $this->actingAs($this->user)
        ->post(route('admin.shipping-partners.store'), [
            'name' => 'Maersk',
            'website_url' => 'maersk',
        ])
        ->assertSessionHasErrors('website_url');
});

test('nama ikon keunggulan hanya menerima format lucide', function () {
    $this->actingAs($this->user)
        ->post(route('admin.features.store'), [
            'title' => ['id' => 'Kualitas'],
            'icon' => 'ShieldCheck',
        ])
        ->assertSessionHasErrors('icon');
});

test('seeder mengisi konten perusahaan tanpa duplikat', function () {
    $this->seed(CompanyContentSeeder::class);
    $this->seed(CompanyContentSeeder::class);

    expect(ProductionStep::query()->count())->toBe(6)
        ->and(QcProtocol::query()->count())->toBe(5)
        ->and(Feature::query()->count())->toBe(4)
        ->and(ShippingPartner::query()->count())->toBe(4)
        ->and(TeamMember::query()->count())->toBe(3);
});
