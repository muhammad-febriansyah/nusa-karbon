<?php

declare(strict_types=1);

use App\Models\ContainerType;
use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSpecification;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('katalog hanya memuat produk aktif', function () {
    Product::factory()->create(['is_active' => true, 'code' => 'TAMPIL']);
    Product::factory()->create(['is_active' => false, 'code' => 'DISEMBUNYIKAN']);

    $this->get(route('public.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/products/index')
            ->has('products.data', 1)
            ->where('products.data.0.code', 'TAMPIL')
            ->where('activeCategory', null),
        );
});

test('saringan kategori hanya menampilkan kategori yang punya produk aktif', function () {
    $dipakai = ProductCategory::factory()->create([
        'name' => ['id' => 'Arang Shisha'],
        'slug' => ['id' => 'arang-shisha'],
    ]);
    // Tidak punya produk aktif sama sekali; tombolnya akan selalu berujung
    // daftar kosong, jadi tidak boleh ikut tampil.
    ProductCategory::factory()->create([
        'name' => ['id' => 'Kategori Kosong'],
        'slug' => ['id' => 'kategori-kosong'],
    ]);

    Product::factory()->create([
        'is_active' => true,
        'product_category_id' => $dipakai->id,
    ]);

    $this->get(route('public.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('categories', 1)
            ->where('categories.0.slug', 'arang-shisha'),
        );
});

test('katalog tersaring saat kategori dikirim lewat query', function () {
    $shisha = ProductCategory::factory()->create([
        'slug' => ['id' => 'arang-shisha'],
    ]);
    $bbq = ProductCategory::factory()->create(['slug' => ['id' => 'arang-bbq']]);

    Product::factory()->create([
        'is_active' => true,
        'code' => 'SHISHA',
        'product_category_id' => $shisha->id,
    ]);
    Product::factory()->create([
        'is_active' => true,
        'code' => 'BBQ',
        'product_category_id' => $bbq->id,
    ]);

    $this->get(route('public.products.index', ['kategori' => 'arang-shisha']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 1)
            ->where('products.data.0.code', 'SHISHA')
            ->where('activeCategory', 'arang-shisha'),
        );
});

test('kategori tak dikenal jatuh ke katalog penuh, bukan daftar kosong', function () {
    Product::factory()->count(2)->create(['is_active' => true]);

    $this->get(route('public.products.index', ['kategori' => 'tidak-ada']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 2)
            ->where('activeCategory', null),
        );
});

test('detail produk mengirim spesifikasi, master carton, dan muatan kontainer', function () {
    $product = Product::factory()->calculable()->create([
        'is_active' => true,
        'slug' => ['id' => 'briket-shisha-cube-25-mm'],
    ]);

    ProductSpecification::factory()->highlighted()->for($product)->create([
        'parameter' => ['id' => 'Fixed Carbon'],
        'value' => '83',
        'unit' => '% min',
    ]);

    $packaging = PackagingOption::factory()->create(['is_active' => true]);
    $product->packagingOptions()->attach($packaging);

    ContainerType::factory()->create(['code' => '20FT_DV']);

    $this->get(route('public.products.show', 'briket-shisha-cube-25-mm'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/products/show')
            ->where('product.slug', 'briket-shisha-cube-25-mm')
            ->has('product.specifications', 1)
            ->where('product.specifications.0.parameter', 'Fixed Carbon')
            ->where('product.specifications.0.is_highlighted', true)
            /*
             * Nilainya, bukan tipenya. Kolom decimal Eloquent dikembalikan
             * sebagai string "20.000"; dibiarkan begitu, angka di frontend akan
             * dibandingkan dan diurutkan sebagai teks. Membandingkan dengan
             * angka 20 gagal kalau castnya hilang, dan itulah yang perlu
             * dijaga — `whereType` tidak bisa dipakai di sini karena
             * `json_encode(20.0)` menghasilkan `20` dan kembali terbaca int.
             */
            ->where('product.master_carton.net_weight_kg', 20)
            ->has('product.packaging_options', 1)
            ->has('containerLoads', 1)
            ->where('containerLoads.0.code', '20FT_DV'),
        );
});

test('produk nonaktif tidak bisa dibuka lewat slug', function () {
    Product::factory()->create([
        'is_active' => false,
        'slug' => ['id' => 'produk-nonaktif'],
    ]);

    $this->get(route('public.products.show', 'produk-nonaktif'))
        ->assertNotFound();
});

test('produk serupa diambil dari kategori yang sama tanpa produk itu sendiri', function () {
    $category = ProductCategory::factory()->create();

    Product::factory()->create([
        'is_active' => true,
        'product_category_id' => $category->id,
        'slug' => ['id' => 'produk-dibuka'],
    ]);
    Product::factory()->count(2)->create([
        'is_active' => true,
        'product_category_id' => $category->id,
    ]);

    $this->get(route('public.products.show', 'produk-dibuka'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('related', 2)
            ->where('related.0.slug', fn (?string $slug) => $slug !== 'produk-dibuka'),
        );
});

test('jumlah produk per kategori ikut dikirim ke penyaring', function () {
    $shisha = ProductCategory::factory()->create(['slug' => ['id' => 'arang-shisha']]);

    Product::factory()->count(3)->create([
        'is_active' => true,
        'product_category_id' => $shisha->id,
    ]);
    Product::factory()->create([
        'is_active' => false,
        'product_category_id' => $shisha->id,
    ]);

    $this->get(route('public.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Yang nonaktif tidak ikut dihitung; angka yang lebih besar dari
            // isi sebenarnya membuat pembeli menekan lalu menemukan kurang.
            ->where('categories.0.total', 3)
            ->where('totalActive', 3),
        );
});

test('urutan MOQ menaruh yang terkecil lebih dulu', function () {
    Product::factory()->create([
        'is_active' => true,
        'code' => 'BESAR',
        'moq_kg' => 5000,
    ]);
    Product::factory()->create([
        'is_active' => true,
        'code' => 'KECIL',
        'moq_kg' => 500,
    ]);
    // Tanpa MOQ: didorong ke belakang, bukan dianggap nol.
    Product::factory()->create([
        'is_active' => true,
        'code' => 'TANPA-MOQ',
        'moq_kg' => null,
    ]);

    $this->get(route('public.products.index', ['urut' => 'moq']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.code', 'KECIL')
            ->where('products.data.1.code', 'BESAR')
            ->where('products.data.2.code', 'TANPA-MOQ')
            ->where('activeSort', 'moq'),
        );
});

test('urutan ukuran membandingkan angkanya, bukan teksnya', function () {
    Product::factory()->create([
        'is_active' => true,
        'code' => 'TIGA-PULUH',
        'size_mm' => '30 x 30 x 30',
    ]);
    // Sebagai teks, "8" berada sesudah "30" — persis kesalahan yang dijaga.
    Product::factory()->create([
        'is_active' => true,
        'code' => 'DELAPAN',
        'size_mm' => '8 x 8 x 8',
    ]);

    $this->get(route('public.products.index', ['urut' => 'ukuran']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.code', 'DELAPAN')
            ->where('products.data.1.code', 'TIGA-PULUH'),
        );
});

test('urutan tak dikenal jatuh ke urutan katalog', function () {
    Product::factory()->create(['is_active' => true]);

    // `urut` masuk ke query builder; nilai bebas dari URL tidak boleh diterima.
    $this->get(route('public.products.index', ['urut' => 'id; drop table products']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('activeSort', 'urutan'));
});

test('katalog dipecah per halaman', function () {
    Product::factory()->count(15)->create(['is_active' => true]);

    $this->get(route('public.products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Dua belas mengisi empat baris penuh pada kisi tiga kolom.
            ->has('products.data', 12)
            ->where('products.meta.current_page', 1)
            ->where('products.meta.last_page', 2)
            ->where('products.meta.total', 15),
        );

    $this->get(route('public.products.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products.data', 3)
            ->where('products.meta.current_page', 2),
        );
});

test('nomor halaman di luar rentang dijepit ke halaman terakhir', function () {
    Product::factory()->count(15)->create(['is_active' => true]);

    /*
     * Tautan lama atau angka yang diketik sendiri lebih baik mendarat di
     * halaman terakhir daripada di kisi kosong yang terbaca seperti katalog
     * habis.
     */
    $this->get(route('public.products.index', ['page' => 99]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.meta.current_page', 2)
            ->has('products.data', 3),
        );
});

test('urutan tetap berlaku lintas halaman', function () {
    foreach (range(1, 15) as $moq) {
        Product::factory()->create([
            'is_active' => true,
            'code' => 'MOQ-'.str_pad((string) $moq, 2, '0', STR_PAD_LEFT),
            'moq_kg' => $moq * 100,
        ]);
    }

    /*
     * Pengurutan dikerjakan sebelum pemotongan halaman. Kalau dibalik, yang
     * terurut hanya isi tiap halaman dan urutan antar halaman jadi acak.
     */
    $this->get(route('public.products.index', ['urut' => 'moq', 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.data.0.code', 'MOQ-13')
            ->where('products.data.2.code', 'MOQ-15'),
        );
});
