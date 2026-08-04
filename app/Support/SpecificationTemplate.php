<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Sembilan parameter spesifikasi yang selalu ditanyakan buyer briket.
 * Dipakai tombol "Isi dari template" di form produk.
 *
 * Nama parameter tidak diterjemahkan — Fixed Carbon, Ash Content, dan
 * sejenisnya adalah istilah dagang.
 *
 * @see docs/CLAUDE.md bagian "Multi-bahasa"
 */
final class SpecificationTemplate
{
    /**
     * @return array<int, array{parameter: string, value: string, unit: string|null, is_highlighted: bool}>
     */
    public static function standard(): array
    {
        return [
            ['parameter' => 'Fixed Carbon', 'value' => '', 'unit' => '% min', 'is_highlighted' => true],
            ['parameter' => 'Ash Content', 'value' => '', 'unit' => '% max', 'is_highlighted' => true],
            ['parameter' => 'Moisture Content', 'value' => '', 'unit' => '% max', 'is_highlighted' => false],
            ['parameter' => 'Volatile Matter', 'value' => '', 'unit' => '% max', 'is_highlighted' => false],
            ['parameter' => 'Calorific Value', 'value' => '', 'unit' => 'kcal/kg', 'is_highlighted' => true],
            ['parameter' => 'Burning Time', 'value' => '', 'unit' => 'menit', 'is_highlighted' => false],
            ['parameter' => 'Density', 'value' => '', 'unit' => 'g/cm³', 'is_highlighted' => false],
            ['parameter' => 'Sulphur Content', 'value' => '', 'unit' => '% max', 'is_highlighted' => false],
            ['parameter' => 'Odour', 'value' => '', 'unit' => null, 'is_highlighted' => false],
        ];
    }
}
