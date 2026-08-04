<?php

declare(strict_types=1);

use App\Exceptions\IncompleteMasterCartonException;
use App\Models\ContainerType;
use App\Models\Product;
use App\Services\ContainerCalculatorService;

/**
 * @param  array<string, mixed>  $attributes
 */
function makeCalculatorProduct(array $attributes = []): Product
{
    return Product::factory()->make([
        'product_category_id' => null,
        ...$attributes,
    ]);
}

/**
 * Kontainer 20FT sesuai `ContainerTypeSeeder`: payload 18.000 kg, 28 CBM.
 *
 * @param  array<string, mixed>  $attributes
 */
function makeCalculatorContainer(array $attributes = []): ContainerType
{
    return ContainerType::factory()->make($attributes);
}

beforeEach(function () {
    $this->calculator = new ContainerCalculatorService;
});

test('produk padat dibatasi berat', function () {
    $product = makeCalculatorProduct([
        'code' => 'DENSE-30',
        'master_carton_weight_kg' => 30.000,
        'master_carton_tare_kg' => 1.500,
        'master_carton_cbm' => 0.0300,
    ]);

    // 18000 / 30 = 600 karton; 28 / 0.03 = 933 karton.
    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result)->toBe([
        'cartons' => 600,
        'net_weight_kg' => 18000.0,
        'gross_weight_kg' => 18900.0,
        'volume_cbm' => 18.0,
        'utilisation' => 1.0,
        'limited_by' => 'weight',
    ]);
});

test('produk ringan bervolume besar dibatasi volume', function () {
    $product = makeCalculatorProduct([
        'code' => 'BULKY-15',
        'master_carton_weight_kg' => 15.000,
        'master_carton_tare_kg' => 1.300,
        'master_carton_cbm' => 0.0430,
    ]);

    // 18000 / 15 = 1200 karton; 28 / 0.043 = 651 karton.
    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result)->toBe([
        'cartons' => 651,
        'net_weight_kg' => 9765.0,
        'gross_weight_kg' => 10611.3,
        'volume_cbm' => 27.993,
        // 27,993 / 28 = 0,99974999… — di bawah setengah, jadi dibulatkan turun.
        'utilisation' => 0.9997,
        'limited_by' => 'volume',
    ]);
});

test('kontainer berbeda bisa membalik faktor pembatas untuk produk yang sama', function () {
    $product = makeCalculatorProduct([
        'code' => 'SHISHA-CUBE-26',
        'master_carton_weight_kg' => 20.000,
        'master_carton_tare_kg' => 1.200,
        'master_carton_cbm' => 0.0420,
    ]);

    $twentyFoot = $this->calculator->calculate($product, makeCalculatorContainer());
    $fortyFoot = $this->calculator->calculate(
        $product,
        makeCalculatorContainer(['max_payload_kg' => 26000, 'max_cbm' => 68]),
    );

    expect($twentyFoot['limited_by'])->toBe('volume')
        ->and($twentyFoot['cartons'])->toBe(666)
        ->and($fortyFoot['limited_by'])->toBe('weight')
        ->and($fortyFoot['cartons'])->toBe(1300);
});

test('batas berat dan volume yang seri dihitung sebagai weight', function () {
    // 18000 / 18 = 1000 karton; 28 / 0.028 = 1000 karton.
    $product = makeCalculatorProduct([
        'code' => 'TIE-1000',
        'master_carton_weight_kg' => 18.000,
        'master_carton_tare_kg' => 0,
        'master_carton_cbm' => 0.0280,
    ]);

    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result['cartons'])->toBe(1000)
        ->and($result['limited_by'])->toBe('weight')
        ->and($result['utilisation'])->toBe(1.0);
});

test('utilisation tidak pernah melebihi satu', function () {
    $product = makeCalculatorProduct([
        'code' => 'EXACT-FIT',
        'master_carton_weight_kg' => 1.000,
        'master_carton_tare_kg' => 0,
        'master_carton_cbm' => 0.0010,
    ]);

    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result['utilisation'])->toBeLessThanOrEqual(1.0)
        ->and($result['utilisation'])->toBe(1.0);
});

test('jumlah karton selalu dibulatkan ke bawah', function () {
    // 18000 / 7.7 = 2337,66 dan 28 / 0.0125 = 2240 — keduanya harus dipotong.
    $product = makeCalculatorProduct([
        'code' => 'FLOOR-CHECK',
        'master_carton_weight_kg' => 7.700,
        'master_carton_tare_kg' => 0.450,
        'master_carton_cbm' => 0.0125,
    ]);

    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result['cartons'])->toBe(2240)
        ->and($result['limited_by'])->toBe('volume')
        ->and($result['net_weight_kg'])->toBe(17248.0)
        ->and($result['gross_weight_kg'])->toBe(18256.0)
        ->and($result['volume_cbm'])->toBe(28.0);
});

test('hasil dibulatkan sesuai presisi tiap kolom', function () {
    // Angka sengaja dipilih supaya tiap kolom punya sisa desimal di luar presisinya.
    $product = makeCalculatorProduct([
        'code' => 'ROUND-CHECK',
        'master_carton_weight_kg' => 20.005,
        'master_carton_tare_kg' => 1.234,
        'master_carton_cbm' => 0.0433,
    ]);

    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    // 28 / 0.0433 = 646 karton; 18000 / 20.005 = 899 karton.
    expect($result['cartons'])->toBe(646)
        ->and($result['net_weight_kg'])->toBe(12923.23)
        ->and($result['gross_weight_kg'])->toBe(13720.39)
        ->and($result['volume_cbm'])->toBe(27.972)
        ->and($result['utilisation'])->toBe(0.999)
        ->and($result['limited_by'])->toBe('volume');
});

test('tare kosong dianggap nol sehingga gross sama dengan net', function () {
    $product = makeCalculatorProduct([
        'code' => 'NO-TARE',
        'master_carton_weight_kg' => 20.000,
        'master_carton_tare_kg' => null,
        'master_carton_cbm' => 0.0420,
    ]);

    $result = $this->calculator->calculate($product, makeCalculatorContainer());

    expect($result['gross_weight_kg'])->toBe($result['net_weight_kg']);
});

test('produk tanpa data master carton ditolak', function (?float $weight, ?float $cbm) {
    $product = makeCalculatorProduct([
        'code' => 'INCOMPLETE-01',
        'master_carton_weight_kg' => $weight,
        'master_carton_cbm' => $cbm,
    ]);

    expect(fn () => $this->calculator->calculate($product, makeCalculatorContainer()))
        ->toThrow(
            IncompleteMasterCartonException::class,
            'Produk INCOMPLETE-01 belum memiliki data master carton yang lengkap.',
        );
})->with([
    'keduanya kosong' => [null, null],
    'berat kosong' => [null, 0.0420],
    'volume kosong' => [20.000, null],
    'berat nol' => [0.0, 0.0420],
    'volume nol' => [20.000, 0.0],
]);

test('scope calculable menyaring produk yang datanya belum lengkap', function () {
    Product::factory()->calculable()->create(['code' => 'READY-01']);
    Product::factory()->create(['code' => 'INCOMPLETE-02']);
    Product::factory()->calculable()->inactive()->create(['code' => 'READY-BUT-OFF']);

    expect(Product::query()->calculable()->pluck('code')->all())->toBe(['READY-01']);
});
