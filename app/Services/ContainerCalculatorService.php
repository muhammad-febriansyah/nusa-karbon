<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\IncompleteMasterCartonException;
use App\Models\ContainerType;
use App\Models\Product;

/**
 * Estimasi muatan satu produk ke dalam satu kontainer.
 *
 * Jumlah karton dibatasi oleh berat atau volume — mana yang lebih dulu habis.
 * Perhitungan yang sama diduplikasi di `resources/js/lib/container-calculator.ts`
 * supaya kalkulator publik responsif tanpa round-trip; sisi PHP tetap menjadi
 * sumber kebenaran dan yang diuji.
 *
 * @see docs/06-architecture.md bagian 5.5
 */
final readonly class ContainerCalculatorService
{
    /**
     * @return array{cartons:int, net_weight_kg:float, gross_weight_kg:float, volume_cbm:float, utilisation:float, limited_by:string}
     *
     * @throws IncompleteMasterCartonException bila berat atau volume master carton kosong
     */
    public function calculate(Product $product, ContainerType $container): array
    {
        $cartonWeight = (float) $product->master_carton_weight_kg;
        $cartonCbm = (float) $product->master_carton_cbm;
        $cartonTare = (float) ($product->master_carton_tare_kg ?? 0);

        if ($cartonWeight <= 0 || $cartonCbm <= 0) {
            throw new IncompleteMasterCartonException($product);
        }

        $maxPayload = (float) $container->max_payload_kg;
        $maxCbm = (float) $container->max_cbm;

        $byWeight = (int) floor($maxPayload / $cartonWeight);
        $byVolume = (int) floor($maxCbm / $cartonCbm);

        $cartons = min($byWeight, $byVolume);
        $limitedBy = $byWeight <= $byVolume ? 'weight' : 'volume';
        $netWeight = $cartons * $cartonWeight;
        $volume = $cartons * $cartonCbm;

        $utilisation = $limitedBy === 'weight'
            ? $netWeight / $maxPayload
            : $volume / $maxCbm;

        return [
            'cartons' => $cartons,
            'net_weight_kg' => round($netWeight, 2),
            'gross_weight_kg' => round($netWeight + ($cartons * $cartonTare), 2),
            'volume_cbm' => round($volume, 3),
            'utilisation' => round(min($utilisation, 1.0), 4),
            'limited_by' => $limitedBy,
        ];
    }
}
