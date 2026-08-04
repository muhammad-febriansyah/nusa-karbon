<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\QcProtocol;
use Database\Seeders\PageBlockSeeder;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('info ekspor mengirim blok halaman dipetakan per kunci', function () {
    $this->seed(PageBlockSeeder::class);

    $this->get(route('public.export-info'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/export-info')
            ->has('blocks.intro')
            ->has('blocks.moq')
            ->has('blocks.payment_terms')
            // Teks sudah diselesaikan ke locale aktif, bukan peta terjemahan.
            ->whereType('blocks.intro.title', 'string'),
        );
});

test('protokol mutu nonaktif tidak ikut tampil', function () {
    QcProtocol::factory()->create([
        'is_active' => true,
        'title' => ['id' => 'Kadar Abu'],
        'result_value' => 'Maks 2,5%',
    ]);
    QcProtocol::factory()->create(['is_active' => false]);

    $this->get(route('public.export-info'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('qcProtocols', 1)
            ->where('qcProtocols.0.title', 'Kadar Abu')
            ->where('qcProtocols.0.result_value', 'Maks 2,5%'),
        );
});

test('faq dikelompokkan per kategori dan yang nonaktif dibuang', function () {
    $pengiriman = FaqCategory::factory()->create([
        'is_active' => true,
        'sort_order' => 0,
        'name' => ['id' => 'Pengiriman'],
    ]);
    $pembayaran = FaqCategory::factory()->create([
        'is_active' => true,
        'sort_order' => 1,
        'name' => ['id' => 'Pembayaran'],
    ]);

    Faq::factory()->for($pengiriman, 'category')->create([
        'is_active' => true,
        'question' => ['id' => 'Berapa lama pengiriman?'],
    ]);
    Faq::factory()->for($pembayaran, 'category')->create([
        'is_active' => true,
        'question' => ['id' => 'Termin bayarnya apa?'],
    ]);
    Faq::factory()->for($pembayaran, 'category')->create(['is_active' => false]);

    $this->get(route('public.export-info'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Satu dari tiga pertanyaan nonaktif, jadi tersisa dua.
            ->has('faqs', 2)
            ->where('faqs.0.category', 'Pengiriman')
            ->where('faqs.0.question', 'Berapa lama pengiriman?')
            // Kategorinya ikut tiap baris, bukan jadi judul kelompok sendiri.
            ->where('faqs.1.category', 'Pembayaran'),
        );
});

test('pertanyaan nonaktif tidak pernah ikut terkirim', function () {
    $kategori = FaqCategory::factory()->create([
        'is_active' => true,
        'name' => ['id' => 'Kategori Sunyi'],
    ]);

    Faq::factory()->for($kategori, 'category')->create(['is_active' => false]);

    $this->get(route('public.export-info'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('faqs', 0));
});

test('info ekspor tetap tampil ketika belum ada konten sama sekali', function () {
    $this->get(route('public.export-info'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('qcProtocols', 0)
            ->has('faqs', 0)
            ->has('shippingPartners', 0),
        );
});
