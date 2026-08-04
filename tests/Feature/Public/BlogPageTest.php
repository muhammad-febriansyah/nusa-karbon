<?php

declare(strict_types=1);

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostCategory;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('arsip blog hanya memuat artikel yang sudah terbit', function () {
    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'title' => ['id' => 'Sudah Terbit'],
    ]);
    Post::factory()->create(['status' => PostStatus::Draft]);
    Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->addWeek(),
    ]);

    $this->get(route('public.blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/blog/index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Sudah Terbit')
            ->where('activeCategory', null),
        );
});

test('saringan kategori hanya menampilkan kategori yang punya artikel terbit', function () {
    $dipakai = PostCategory::factory()->create([
        'name' => ['id' => 'Panduan Ekspor'],
        'slug' => ['id' => 'panduan-ekspor'],
    ]);
    // Tidak punya artikel terbit; tombolnya akan selalu berujung daftar kosong.
    PostCategory::factory()->create(['slug' => ['id' => 'kategori-sunyi']]);

    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'post_category_id' => $dipakai->id,
    ]);

    $this->get(route('public.blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('categories', 1)
            ->where('categories.0.slug', 'panduan-ekspor')
            // Jumlahnya tertulis di label pilihan; tanpa itu pembaca menekan
            // kategori tanpa tahu isinya satu artikel atau dua puluh.
            ->where('categories.0.total', 1)
            ->where('totalPublished', 1),
        );
});

test('arsip tersaring saat kategori dikirim lewat query', function () {
    $panduan = PostCategory::factory()->create([
        'slug' => ['id' => 'panduan-ekspor'],
    ]);
    $produk = PostCategory::factory()->create([
        'slug' => ['id' => 'pengetahuan-produk'],
    ]);

    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'post_category_id' => $panduan->id,
        'title' => ['id' => 'Artikel Panduan'],
    ]);
    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'post_category_id' => $produk->id,
    ]);

    $this->get(route('public.blog.index', ['kategori' => 'panduan-ekspor']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Artikel Panduan')
            ->where('activeCategory', 'panduan-ekspor'),
        );
});

test('artikel terbit bisa dibuka lewat slug beserta isinya', function () {
    Post::factory()->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'slug' => ['id' => 'cara-membaca-spesifikasi'],
        'title' => ['id' => 'Cara Membaca Spesifikasi'],
        'content' => ['id' => '<p>Isi artikel.</p>'],
    ]);

    $this->get(route('public.blog.show', 'cara-membaca-spesifikasi'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/blog/show')
            ->where('post.title', 'Cara Membaca Spesifikasi')
            // `content` sengaja tidak ikut di kartu, tetapi wajib ada di sini.
            ->where('post.content', '<p>Isi artikel.</p>')
            ->has('post.tags'),
        );
});

test('draf dan artikel terjadwal tidak bisa dibuka lewat slug', function () {
    Post::factory()->create([
        'status' => PostStatus::Draft,
        'slug' => ['id' => 'masih-draf'],
    ]);
    Post::factory()->create([
        'status' => PostStatus::Scheduled,
        'published_at' => now()->addWeek(),
        'slug' => ['id' => 'belum-waktunya'],
    ]);

    $this->get(route('public.blog.show', 'masih-draf'))->assertNotFound();
    $this->get(route('public.blog.show', 'belum-waktunya'))->assertNotFound();
});

test('arsip blog dipecah per halaman', function () {
    Post::factory()->count(12)->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('public.blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Satu artikel utama plus enam di kisi tiga kolom.
            ->has('posts.data', 7)
            ->where('posts.meta.current_page', 1)
            ->where('posts.meta.last_page', 2)
            ->where('posts.meta.total', 12),
        );

    $this->get(route('public.blog.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 5)
            ->where('posts.meta.current_page', 2),
        );
});

test('artikel terbaru tetap paling depan lintas halaman', function () {
    foreach (range(1, 12) as $day) {
        Post::factory()->create([
            'status' => PostStatus::Published,
            // Hari ke-12 adalah yang paling baru.
            'published_at' => now()->subDays(13 - $day),
            'title' => ['id' => 'Artikel '.str_pad((string) $day, 2, '0', STR_PAD_LEFT)],
        ]);
    }

    $this->get(route('public.blog.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('posts.data.0.title', 'Artikel 12'),
        );

    $this->get(route('public.blog.index', ['page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Lima tertua berada di halaman terakhir, bukan tercampur.
            ->where('posts.data.0.title', 'Artikel 05')
            ->where('posts.data.4.title', 'Artikel 01'),
        );
});

test('saringan kategori tetap terbawa saat berpindah halaman', function () {
    $kategori = PostCategory::factory()->create([
        'slug' => ['id' => 'panduan-ekspor'],
    ]);

    Post::factory()->count(12)->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
        'post_category_id' => $kategori->id,
    ]);
    Post::factory()->count(5)->create([
        'status' => PostStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('public.blog.index', [
        'kategori' => 'panduan-ekspor',
        'page' => 2,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('activeCategory', 'panduan-ekspor')
            ->where('posts.meta.total', 12)
            ->has('posts.data', 5),
        );
});
