<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\IncompleteMasterCartonException;
use App\Models\ContainerType;
use App\Models\Product;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Support\Locale;

/**
 * Menjalankan Container Calculator satu produk terhadap seluruh kontainer aktif,
 * untuk pratinjau di halaman detail produk admin.
 *
 * Perhitungannya tetap milik `ContainerCalculatorService`; kelas ini hanya
 * memutar daftar kontainer dan menerjemahkan hasilnya untuk tampilan.
 */
final readonly class ContainerLoadService
{
    public function __construct(
        private ContainerCalculatorService $calculator,
        private ContainerTypeRepositoryInterface $containers,
    ) {}

    /**
     * @return array<int, array{
     *     container_id:int,
     *     code:string,
     *     name:string,
     *     max_payload_kg:float,
     *     max_cbm:float,
     *     cartons:int,
     *     net_weight_kg:float,
     *     gross_weight_kg:float,
     *     volume_cbm:float,
     *     utilisation:float,
     *     limited_by:string
     * }>
     */
    public function forProduct(Product $product): array
    {
        if (! $product->isCalculable()) {
            return [];
        }

        $locale = Locale::default();

        return $this->containers->activeOrdered()
            ->map(function (ContainerType $container) use ($product, $locale): ?array {
                try {
                    $result = $this->calculator->calculate($product, $container);
                } catch (IncompleteMasterCartonException) {
                    // Sudah disaring `isCalculable()`; dijaga agar satu kontainer
                    // bermasalah tidak menggagalkan seluruh halaman.
                    return null;
                }

                return [
                    'container_id' => $container->id,
                    'code' => $container->code,
                    'name' => $container->getTranslation('name', $locale),
                    'max_payload_kg' => $container->max_payload_kg,
                    'max_cbm' => $container->max_cbm,
                    ...$result,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
