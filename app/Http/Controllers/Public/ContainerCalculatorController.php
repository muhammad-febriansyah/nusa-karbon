<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContainerType;
use App\Models\Product;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kalkulator muatan kontainer.
 *
 * Seluruh hitungannya berjalan di peramban lewat `lib/container-calculator.ts`,
 * cerminan dari `ContainerCalculatorService`. Halaman ini hanya menyiapkan
 * bahannya: produk yang datanya cukup untuk dihitung, dan kontainer yang aktif.
 *
 * Alasan menghitung di klien: pengunjung mengganti produk dan kontainer
 * berkali-kali saat menyusun pesanan, dan setiap pergantian yang menunggu
 * round-trip membuat alat ini terasa lamban. Sisi PHP tetap sumber kebenaran
 * dan yang diuji — paritasnya dijaga `ContainerCalculatorParityTest`.
 */
final class ContainerCalculatorController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ContainerTypeRepositoryInterface $containers,
    ) {}

    public function __invoke(): Response
    {
        $locale = app()->getLocale();

        return Inertia::render('public/container-calculator', [
            /*
             * Hanya produk yang master carton-nya lengkap. Produk lain akan
             * selalu menghasilkan nol karton, dan menawarkannya di daftar
             * pilihan berarti mengundang pengunjung ke jalan buntu.
             */
            'products' => $this->products->calculable()
                ->map(fn (Product $product): array => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->getTranslation('name', $locale) ?: $product->code,
                    'slug' => $product->getTranslation('slug', $locale) ?: null,
                    'size_mm' => $product->size_mm,
                    'shape' => $product->shape,
                    'master_carton_weight_kg' => (float) $product->master_carton_weight_kg,
                    'master_carton_cbm' => (float) $product->master_carton_cbm,
                    'master_carton_tare_kg' => $product->master_carton_tare_kg === null
                        ? null
                        : (float) $product->master_carton_tare_kg,
                    'moq_kg' => $product->moq_kg === null
                        ? null
                        : (float) $product->moq_kg,
                ])
                ->values()
                ->all(),

            'containers' => $this->containers->activeOrdered()
                ->map(fn (ContainerType $container): array => [
                    'id' => $container->id,
                    'code' => $container->code,
                    'name' => $container->getTranslation('name', $locale),
                    'max_payload_kg' => (float) $container->max_payload_kg,
                    'max_cbm' => (float) $container->max_cbm,
                    'internal_length_m' => $container->internal_length_m === null
                        ? null
                        : (float) $container->internal_length_m,
                    'internal_width_m' => $container->internal_width_m === null
                        ? null
                        : (float) $container->internal_width_m,
                ])
                ->values()
                ->all(),
        ]);
    }
}
