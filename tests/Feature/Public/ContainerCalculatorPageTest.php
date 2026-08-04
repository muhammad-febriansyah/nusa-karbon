<?php

declare(strict_types=1);

use App\Models\ContainerType;
use App\Models\Product;
use Database\Seeders\SiteSettingSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('kalkulator hanya menawarkan produk yang master carton-nya lengkap', function () {
    Product::factory()->calculable()->create([
        'is_active' => true,
        'code' => 'BISA-DIHITUNG',
    ]);
    // Master carton kosong: apa pun kontainernya hasilnya selalu nol karton,
    // jadi menawarkannya di daftar pilihan hanya mengundang jalan buntu.
    Product::factory()->create([
        'is_active' => true,
        'code' => 'BELUM-LENGKAP',
    ]);

    ContainerType::factory()->create();

    $this->get(route('public.container-calculator'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/container-calculator')
            ->has('products', 1)
            ->where('products.0.code', 'BISA-DIHITUNG')
            /*
             * Decimal Eloquent dikembalikan sebagai string. Kalkulator di
             * peramban membaginya langsung, dan pembagian string dengan string
             * menghasilkan angka yang salah tanpa satu pun galat.
             */
            ->where('products.0.master_carton_weight_kg', 20)
            ->where('products.0.master_carton_cbm', 0.043),
        );
});

test('kontainer nonaktif tidak ikut ke kalkulator', function () {
    Product::factory()->calculable()->create(['is_active' => true]);

    ContainerType::factory()->create(['code' => 'AKTIF']);
    ContainerType::factory()->inactive()->create(['code' => 'NONAKTIF']);

    $this->get(route('public.container-calculator'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('containers', 1)
            ->where('containers.0.code', 'AKTIF'),
        );
});

test('kalkulator tetap terbuka meski belum ada data sama sekali', function () {
    $this->get(route('public.container-calculator'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('products', 0)
            ->has('containers', 0),
        );
});
