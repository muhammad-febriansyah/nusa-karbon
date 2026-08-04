/**
 * Harness parity untuk `tests/Feature/ContainerCalculatorParityTest.php`.
 *
 * Membaca daftar kasus (JSON) dari stdin, menjalankan kalkulator sisi klien,
 * lalu menulis hasilnya sebagai JSON ke stdout supaya PHP bisa membandingkan
 * angkanya. Dijalankan langsung oleh Node lewat type stripping bawaan — tidak
 * ada langkah build dan tidak ada dependensi tambahan.
 *
 * Berkas ini hanya dipakai pengujian dan tidak ikut ke bundel Vite.
 */
import { readFileSync } from 'node:fs';

import { calculateContainerLoad } from '../../resources/js/lib/container-calculator.ts';
import type {
    CalculableContainer,
    CalculableProduct,
} from '../../resources/js/lib/container-calculator.ts';

type Case = {
    key: string;
    product: CalculableProduct;
    container: CalculableContainer;
};

const cases: Case[] = JSON.parse(readFileSync(0, 'utf8'));

const results: Record<string, unknown> = {};

for (const testCase of cases) {
    try {
        results[testCase.key] = calculateContainerLoad(
            testCase.product,
            testCase.container,
        );
    } catch (error) {
        results[testCase.key] = {
            error: error instanceof Error ? error.message : String(error),
        };
    }
}

process.stdout.write(JSON.stringify(results));
