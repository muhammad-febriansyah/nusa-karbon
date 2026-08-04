<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\ProductionStep;
use App\Models\TeamMember;
use Database\Seeders\PageBlockSeeder;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('tentang kami mengirim blok halaman dipetakan per kunci', function () {
    $this->seed(PageBlockSeeder::class);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/about')
            ->has('blocks.company_profile')
            ->has('blocks.supply_chain_position')
            ->has('blocks.why_trader')
            // Teks sudah diselesaikan ke locale aktif, bukan peta terjemahan.
            ->whereType('blocks.company_profile.title', 'string'),
        );
});

test('anggota tim nonaktif tidak ikut tampil', function () {
    TeamMember::factory()->create(['is_active' => true, 'name' => 'Tampil']);
    TeamMember::factory()->create(['is_active' => false, 'name' => 'Disembunyikan']);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.data', 1)
            ->where('team.data.0.name', 'Tampil'),
        );
});

test('halaman ini memuat seluruh sertifikat aktif, bukan hanya trust bar', function () {
    // Trust bar di beranda disaring karena ruangnya sempit; halaman ini justru
    // tempat daftar lengkapnya diperiksa.
    Certificate::factory()->create([
        'is_active' => true,
        'show_in_trust_bar' => true,
    ]);
    Certificate::factory()->create([
        'is_active' => true,
        'show_in_trust_bar' => false,
    ]);
    Certificate::factory()->create(['is_active' => false]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('certificates', 2));
});

test('tentang kami tetap tampil ketika belum ada konten sama sekali', function () {
    ProductionStep::factory()->create(['is_active' => false]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.data', 0)
            ->has('certificates', 0)
            ->has('productionSteps', 0),
        );
});

test('roster tim dipecah per halaman', function () {
    TeamMember::factory()->count(20)->create(['is_active' => true]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Delapan per halaman: satu layar penuh tanpa menggulir, dan tiga
            // puluh orang jadi empat halaman alih-alih satu dinding wajah.
            ->has('team.data', 8)
            ->where('team.meta.current_page', 1)
            ->where('team.meta.last_page', 3)
            ->where('team.meta.total', 20),
        );

    $this->get(route('public.about', ['tim' => 3]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('team.data', 4)
            ->where('team.meta.current_page', 3),
        );
});

test('sertifikat mengirim masa berlakunya', function () {
    Certificate::factory()->create([
        'is_active' => true,
        'certificate_number' => 'NC/2026/0001',
        'expires_at' => now()->addYears(3),
    ]);

    $this->get(route('public.about'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('certificates.0.certificate_number', 'NC/2026/0001')
            // Nomor tanpa masa berlaku tidak bisa diverifikasi ke penerbitnya.
            ->whereType('certificates.0.expires_at', 'string'),
        );
});
