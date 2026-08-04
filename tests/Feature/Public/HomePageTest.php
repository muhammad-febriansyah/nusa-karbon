<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Models\Certificate;
use App\Models\ContainerType;
use App\Models\Feature;
use App\Models\PackagingOption;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductionStep;
use App\Models\ProductSpecification;
use App\Models\ShippingPartner;
use Database\Seeders\PageBlockSeeder;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('beranda bisa diakses tamu', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('public/home'));
});

test('beranda mengirim blok halaman dipetakan per kunci', function () {
    $this->seed(PageBlockSeeder::class);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('blocks.hero')
            ->has('blocks.cta')
            ->where('blocks.hero.key', 'hero')
            // Teks sudah diselesaikan ke locale aktif, bukan peta terjemahan.
            ->whereType('blocks.hero.title', 'string'),
        );
});

test('hanya produk aktif yang tampil, unggulan lebih dulu', function () {
    Product::factory()->create([
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 1,
        'code' => 'BIASA',
    ]);
    Product::factory()->create([
        'is_active' => true,
        'is_featured' => true,
        'sort_order' => 9,
        'code' => 'UNGGULAN',
    ]);
    Product::factory()->create(['is_active' => false, 'code' => 'NONAKTIF']);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 2)
            ->where('products.0.code', 'UNGGULAN'),
        );
});

test('artikel draft dan terjadwal tidak muncul di beranda', function () {
    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
    ]);
    Post::factory()->create(['status' => PostStatus::Draft]);
    Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->addWeek(),
    ]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('latestPosts', 1));
});

test('konten nonaktif tidak ikut ke beranda', function () {
    Feature::factory()->create(['is_active' => true]);
    Feature::factory()->create(['is_active' => false]);
    PackagingOption::factory()->create(['is_active' => true]);
    PackagingOption::factory()->create(['is_active' => false]);
    ShippingPartner::factory()->create(['is_active' => true]);
    ShippingPartner::factory()->create(['is_active' => false]);
    ProductionStep::factory()->create(['is_active' => true]);
    ProductionStep::factory()->create(['is_active' => false]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('features', 1)
            ->has('packagingOptions', 1)
            ->has('shippingPartners', 1)
            ->has('productionSteps', 1),
        );
});

test('tahap produksi dikirim menurut urutan yang disusun admin', function () {
    ProductionStep::factory()->create([
        'is_active' => true,
        'sort_order' => 2,
        'title' => ['id' => 'Pengemasan'],
    ]);
    ProductionStep::factory()->create([
        'is_active' => true,
        'sort_order' => 0,
        'title' => ['id' => 'Seleksi Batok Kelapa'],
    ]);
    ProductionStep::factory()->create([
        'is_active' => true,
        'sort_order' => 1,
        'title' => ['id' => 'Karbonisasi'],
    ]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('productionSteps', 3)
            // Nomor tahap di frontend diturunkan dari posisi dalam larik ini,
            // jadi urutannya adalah kontrak — bukan sekadar kerapian.
            ->where('productionSteps.0.title', 'Seleksi Batok Kelapa')
            ->where('productionSteps.1.title', 'Karbonisasi')
            ->where('productionSteps.2.title', 'Pengemasan'),
        );
});

test('trust bar hanya memuat sertifikat yang ditandai untuk trust bar', function () {
    Certificate::factory()->create([
        'is_active' => true,
        'show_in_trust_bar' => true,
    ]);
    Certificate::factory()->create([
        'is_active' => true,
        'show_in_trust_bar' => false,
    ]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('trustBar', 1));
});

test('beranda tetap tampil ketika belum ada konten sama sekali', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 0)
            ->has('features', 0)
            ->has('trustBar', 0)
            ->has('latestPosts', 0)
            ->where('specSheet', null)
            ->where('containerLoad', null),
        );
});

test('lembar spesifikasi mendahulukan parameter yang ditandai admin', function () {
    $product = Product::factory()->calculable()->create([
        'is_featured' => true,
        'code' => 'ACUAN',
    ]);

    // Ditandai admin, tetapi sengaja diletakkan paling belakang urutannya untuk
    // membuktikan tanda itulah yang menentukan, bukan sort_order.
    ProductSpecification::factory()->highlighted()->for($product)->create([
        'parameter' => ['id' => 'Ash Content'],
        'sort_order' => 8,
    ]);

    foreach (['Moisture', 'Volatile', 'Density', 'Sulphur'] as $index => $name) {
        ProductSpecification::factory()->for($product)->create([
            'parameter' => ['id' => $name],
            'sort_order' => $index,
        ]);
    }

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('specSheet.code', 'ACUAN')
            // Lima spesifikasi tersimpan, empat yang muat di pita hero.
            ->has('specSheet.specs', 4)
            ->where('specSheet.specs.0.parameter', 'Ash Content')
            ->where('specSheet.specs.1.parameter', 'Moisture'),
        );
});

test('produk tanpa data master carton tidak dipakai sebagai acuan', function () {
    // Unggulan dan paling depan, tetapi master carton-nya kosong sehingga
    // muatan kontainernya tidak bisa dihitung.
    Product::factory()->create([
        'is_featured' => true,
        'sort_order' => 0,
        'code' => 'BELUM-LENGKAP',
    ]);
    Product::factory()->calculable()->create([
        'is_featured' => true,
        'sort_order' => 1,
        'code' => 'LENGKAP',
    ]);
    ContainerType::factory()->create();

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.0.code', 'BELUM-LENGKAP')
            ->where('specSheet.code', 'LENGKAP')
            ->where('containerLoad.product.code', 'LENGKAP'),
        );
});

test('muatan beranda dihitung pada kontainer aktif pertama', function () {
    Product::factory()->calculable()->create(['is_featured' => true]);

    ContainerType::factory()->create([
        'code' => 'PERTAMA',
        'sort_order' => 0,
    ]);
    ContainerType::factory()->highCube()->create([
        'code' => 'KEDUA',
        'sort_order' => 1,
    ]);

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('containerLoad.container.code', 'PERTAMA')
            // 28 CBM ÷ 0,043 CBM per karton = 651 karton; batas volume tercapai
            // sebelum batas berat 18.000 kg ÷ 20 kg = 900 karton.
            ->where('containerLoad.cartons', 651)
            ->where('containerLoad.limited_by', 'volume')
            ->where('containerLoad.net_weight_kg', 13020),
        );
});

test('muatan tidak dikirim ketika belum ada kontainer aktif', function () {
    Product::factory()->calculable()->create(['is_featured' => true]);
    ContainerType::factory()->inactive()->create();

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('specSheet')
            ->where('containerLoad', null),
        );
});
