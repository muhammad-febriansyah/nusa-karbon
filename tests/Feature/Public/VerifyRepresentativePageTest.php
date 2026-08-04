<?php

declare(strict_types=1);

use App\Models\Representative;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('daftar lengkap perwakilan tidak pernah dikirim tanpa kata kunci', function () {
    Representative::factory()->count(3)->create(['is_active' => true]);

    $this->get(route('public.verify-representative'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/verify-representative')
            ->where('searched', false)
            // Nama, email, dan nomor seluruh tim adalah bahan mentah yang
            // berguna justru bagi penipu.
            ->has('results', 0),
        );
});

test('kata kunci terlalu pendek tidak menjalankan pencarian', function () {
    Representative::factory()->create(['is_active' => true, 'name' => 'Yusuf']);

    $this->get(route('public.verify-representative', ['q' => 'Yu']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('searched', false)
            ->has('results', 0),
        );
});

test('perwakilan ditemukan lewat nama', function () {
    Representative::factory()->create([
        'is_active' => true,
        'name' => 'Yusuf Abdullah',
    ]);
    Representative::factory()->create([
        'is_active' => true,
        'name' => 'Orang Lain',
    ]);

    $this->get(route('public.verify-representative', ['q' => 'Yusuf']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('searched', true)
            ->has('results', 1)
            ->where('results.0.name', 'Yusuf Abdullah')
            ->where('results.0.is_current', true),
        );
});

test('nomor WhatsApp cocok meski pemisahnya berbeda', function () {
    Representative::factory()->create([
        'is_active' => true,
        'name' => 'Yusuf',
        'whatsapp' => '+62 812-3456-7890',
    ]);

    // Pembeli menyalin nomor apa adanya dari WhatsApp; bentuknya bisa berbeda
    // dari yang tersimpan, dan pencocokan mentah gagal justru di kasus ini.
    $this->get(route('public.verify-representative', ['q' => '628123456789']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('results', 1));
});

test('email dan nomor tidak ikut dikembalikan ke pengunjung', function () {
    Representative::factory()->create([
        'is_active' => true,
        'name' => 'Yusuf',
        'email' => 'yusuf@nusacarbon.com',
        'whatsapp' => '+628123456789',
    ]);

    $this->get(route('public.verify-representative', ['q' => 'Yusuf']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('results', 1)
            /*
             * Halaman ini menjawab "apakah orang ini resmi", bukan "berikan
             * kontaknya" — mengembalikan email dan nomor akan mengubah alat
             * verifikasi jadi direktori yang bisa dipanen.
             */
            ->missing('results.0.email')
            ->missing('results.0.whatsapp')
            ->missing('results.0.phone'),
        );
});

test('perwakilan nonaktif tidak pernah muncul', function () {
    Representative::factory()->create([
        'is_active' => false,
        'name' => 'Yusuf Mantan',
    ]);

    $this->get(route('public.verify-representative', ['q' => 'Yusuf']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('results', 0));
});

test('mantan perwakilan yang masih terdaftar ditandai sudah tidak mewakili', function () {
    /*
     * Ditampilkan, bukan disembunyikan. Menyembunyikannya membuat pembeli
     * mengira orang itu palsu padahal ia memang pernah resmi — dan celah itulah
     * yang paling sering dipakai untuk menipu.
     */
    Representative::factory()->create([
        'is_active' => true,
        'name' => 'Yusuf Lama',
        'started_at' => now()->subYears(3),
        'ended_at' => now()->subMonths(2),
    ]);

    $this->get(route('public.verify-representative', ['q' => 'Yusuf']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('results', 1)
            ->where('results.0.is_current', false),
        );
});
