/**
 * Cerminan `App\Services\ContainerCalculatorService` di sisi klien. Kalkulator
 * publik memakai berkas ini supaya hasilnya muncul seketika tanpa round-trip;
 * sisi PHP tetap sumber kebenaran dan yang diuji.
 *
 * Bila rumus di service PHP berubah, berkas ini harus ikut diperbarui — parity-
 * nya dijaga `tests/Feature/ContainerCalculatorParityTest.php`.
 *
 * @see app/Services/ContainerCalculatorService.php
 * @see docs/06-architecture.md bagian 5.5
 */

export type CalculableProduct = {
    code: string;
    master_carton_weight_kg: number | null;
    master_carton_cbm: number | null;
    master_carton_tare_kg?: number | null;
};

export type CalculableContainer = {
    code: string;
    max_payload_kg: number;
    max_cbm: number;
};

export type ContainerLoad = {
    cartons: number;
    net_weight_kg: number;
    gross_weight_kg: number;
    volume_cbm: number;
    utilisation: number;
    limited_by: 'weight' | 'volume';
};

export class IncompleteMasterCartonError extends Error {
    readonly productCode: string;

    constructor(productCode: string) {
        super(
            `Produk ${productCode} belum memiliki data master carton yang lengkap.`,
        );
        this.name = 'IncompleteMasterCartonError';
        this.productCode = productCode;
    }
}

/**
 * Menggeser titik desimal lewat notasi eksponen, bukan perkalian, supaya tidak
 * ada galat floating point tambahan sebelum pembulatan.
 */
function shiftExponent(value: number, exponent: number): number {
    const [mantissa, current = '0'] = `${value}`.split('e');

    return Number(`${mantissa}e${Number(current) + exponent}`);
}

/**
 * Meniru `round()` PHP: pembulatan mengikuti representasi desimal terpendek
 * dari nilai double dan setengah dibulatkan menjauh dari nol. `Number.toFixed()`
 * tidak dipakai karena membulatkan berdasarkan nilai biner eksaknya sehingga
 * hasilnya bisa berbeda satu digit dari PHP (contoh: 0.285 → 0.28, bukan 0.29).
 */
function roundTo(value: number, precision: number): number {
    const shifted = shiftExponent(value, precision);
    const rounded = Math.sign(shifted) * Math.round(Math.abs(shifted));

    return shiftExponent(rounded, -precision);
}

export function isCalculable(product: CalculableProduct): boolean {
    return (
        typeof product.master_carton_weight_kg === 'number' &&
        product.master_carton_weight_kg > 0 &&
        typeof product.master_carton_cbm === 'number' &&
        product.master_carton_cbm > 0
    );
}

/**
 * Jumlah karton dibatasi berat atau volume — mana yang lebih dulu habis.
 *
 * @throws {IncompleteMasterCartonError} bila berat atau volume master carton kosong
 */
export function calculateContainerLoad(
    product: CalculableProduct,
    container: CalculableContainer,
): ContainerLoad {
    const cartonWeight = Number(product.master_carton_weight_kg ?? 0);
    const cartonCbm = Number(product.master_carton_cbm ?? 0);
    const cartonTare = Number(product.master_carton_tare_kg ?? 0);

    if (cartonWeight <= 0 || cartonCbm <= 0) {
        throw new IncompleteMasterCartonError(product.code);
    }

    const maxPayload = Number(container.max_payload_kg);
    const maxCbm = Number(container.max_cbm);

    const byWeight = Math.floor(maxPayload / cartonWeight);
    const byVolume = Math.floor(maxCbm / cartonCbm);

    const cartons = Math.min(byWeight, byVolume);
    const limitedBy = byWeight <= byVolume ? 'weight' : 'volume';
    const netWeight = cartons * cartonWeight;
    const volume = cartons * cartonCbm;

    const utilisation =
        limitedBy === 'weight' ? netWeight / maxPayload : volume / maxCbm;

    return {
        cartons,
        net_weight_kg: roundTo(netWeight, 2),
        gross_weight_kg: roundTo(netWeight + cartons * cartonTare, 2),
        volume_cbm: roundTo(volume, 3),
        utilisation: roundTo(Math.min(utilisation, 1), 4),
        limited_by: limitedBy,
    };
}
