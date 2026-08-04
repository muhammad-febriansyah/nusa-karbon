<?php

declare(strict_types=1);

use App\Exceptions\IncompleteMasterCartonException;
use App\Models\ContainerType;
use App\Models\Product;
use App\Services\ContainerCalculatorService;
use Database\Seeders\ContainerTypeSeeder;
use Database\Seeders\ProductCategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\Process;

/**
 * Menjalankan kalkulator versi TypeScript lewat Node (type stripping bawaan,
 * tanpa langkah build) dan mengembalikan hasilnya per kunci kasus.
 *
 * @param  list<array{key: string, product: array<string, mixed>, container: array<string, mixed>}>  $cases
 * @return array<string, array<string, mixed>>
 */
function runClientCalculator(array $cases): array
{
    $result = Process::path(base_path())
        ->input(json_encode($cases, JSON_THROW_ON_ERROR))
        ->run(['node', 'tests/Fixtures/container-calculator-parity.ts']);

    expect($result->successful())->toBeTrue(
        'Harness TypeScript gagal dijalankan: '.$result->errorOutput(),
    );

    /** @var array<string, array<string, mixed>> $decoded */
    $decoded = json_decode($result->output(), true, 512, JSON_THROW_ON_ERROR);

    // `JSON.stringify()` menulis 5250.0 sebagai `5250`, sehingga PHP membacanya
    // sebagai int. Tipe dikembalikan dulu supaya perbandingannya benar-benar
    // menguji angkanya, bukan artefak serialisasi JSON.
    return array_map(function (array $load): array {
        if (array_key_exists('error', $load)) {
            return $load;
        }

        return [
            'cartons' => (int) $load['cartons'],
            'net_weight_kg' => (float) $load['net_weight_kg'],
            'gross_weight_kg' => (float) $load['gross_weight_kg'],
            'volume_cbm' => (float) $load['volume_cbm'],
            'utilisation' => (float) $load['utilisation'],
            'limited_by' => $load['limited_by'],
        ];
    }, $decoded);
}

beforeEach(function () {
    if (Process::run(['node', '--version'])->failed()) {
        $this->markTestSkipped('Node tidak tersedia di lingkungan ini.');
    }

    $this->calculator = new ContainerCalculatorService;
});

test('hasil PHP dan TypeScript identik untuk seluruh kombinasi seeder', function () {
    $this->seed([ProductCategorySeeder::class, ContainerTypeSeeder::class, ProductSeeder::class]);

    $products = Product::query()->calculable()->orderBy('code')->get();
    $containers = ContainerType::query()->active()->orderBy('code')->get();

    expect($products)->not->toBeEmpty()
        ->and($containers)->not->toBeEmpty();

    $cases = [];
    $expected = [];

    foreach ($products as $product) {
        foreach ($containers as $container) {
            $key = "{$product->code}|{$container->code}";

            $cases[] = [
                'key' => $key,
                'product' => [
                    'code' => $product->code,
                    'master_carton_weight_kg' => $product->master_carton_weight_kg,
                    'master_carton_cbm' => $product->master_carton_cbm,
                    'master_carton_tare_kg' => $product->master_carton_tare_kg,
                ],
                'container' => [
                    'code' => $container->code,
                    'max_payload_kg' => $container->max_payload_kg,
                    'max_cbm' => $container->max_cbm,
                ],
            ];

            $expected[$key] = $this->calculator->calculate($product, $container);
        }
    }

    // 5 produk calculable × 2 kontainer — pengaman kalau seeder-nya menyusut.
    expect($cases)->toHaveCount(10)
        ->and(runClientCalculator($cases))->toBe($expected);
});

test('hasil PHP dan TypeScript identik untuk angka rawan pembulatan', function () {
    $container = ContainerType::factory()->make();

    // Angka yang hasil baginya jatuh dekat setengah, tempat PHP dan JavaScript
    // paling mudah berbeda satu digit terakhir.
    $edgeCases = [
        ['weight' => 15.000, 'tare' => 1.300, 'cbm' => 0.0430],
        ['weight' => 20.005, 'tare' => 1.234, 'cbm' => 0.0433],
        ['weight' => 7.700, 'tare' => 0.450, 'cbm' => 0.0125],
        ['weight' => 0.285, 'tare' => 0.145, 'cbm' => 0.0007],
        ['weight' => 1.005, 'tare' => 2.675, 'cbm' => 0.0011],
        ['weight' => 8.475, 'tare' => 0.001, 'cbm' => 0.0333],
        ['weight' => 33.333, 'tare' => 1.666, 'cbm' => 0.0666],
        ['weight' => 9999.999, 'tare' => 999.999, 'cbm' => 0.0001],
    ];

    $cases = [];
    $expected = [];

    foreach ($edgeCases as $index => $edgeCase) {
        $key = "EDGE-{$index}";

        $product = Product::factory()->make([
            'product_category_id' => null,
            'code' => $key,
            'master_carton_weight_kg' => $edgeCase['weight'],
            'master_carton_tare_kg' => $edgeCase['tare'],
            'master_carton_cbm' => $edgeCase['cbm'],
        ]);

        $cases[] = [
            'key' => $key,
            'product' => [
                'code' => $product->code,
                'master_carton_weight_kg' => $product->master_carton_weight_kg,
                'master_carton_cbm' => $product->master_carton_cbm,
                'master_carton_tare_kg' => $product->master_carton_tare_kg,
            ],
            'container' => [
                'code' => $container->code,
                'max_payload_kg' => $container->max_payload_kg,
                'max_cbm' => $container->max_cbm,
            ],
        ];

        $expected[$key] = $this->calculator->calculate($product, $container);
    }

    expect(runClientCalculator($cases))->toBe($expected);
});

test('kedua sisi menolak produk tanpa data master carton', function () {
    $container = ContainerType::factory()->make();

    $product = Product::factory()->make([
        'product_category_id' => null,
        'code' => 'INCOMPLETE-PARITY',
        'master_carton_weight_kg' => null,
        'master_carton_cbm' => null,
    ]);

    $clientResult = runClientCalculator([[
        'key' => 'INCOMPLETE-PARITY',
        'product' => [
            'code' => $product->code,
            'master_carton_weight_kg' => null,
            'master_carton_cbm' => null,
            'master_carton_tare_kg' => null,
        ],
        'container' => [
            'code' => $container->code,
            'max_payload_kg' => $container->max_payload_kg,
            'max_cbm' => $container->max_cbm,
        ],
    ]]);

    $serverMessage = null;

    try {
        $this->calculator->calculate($product, $container);
    } catch (IncompleteMasterCartonException $exception) {
        $serverMessage = $exception->getMessage();
    }

    expect($serverMessage)->toBe('Produk INCOMPLETE-PARITY belum memiliki data master carton yang lengkap.')
        ->and($clientResult['INCOMPLETE-PARITY'])->toBe(['error' => $serverMessage]);
});
