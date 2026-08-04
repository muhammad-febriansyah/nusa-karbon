<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Rupiah disimpan `BIGINT` tanpa desimal. Tampilan memakai pemisah ribuan titik.
 *
 * @see docs/06-architecture.md bagian 8
 */
final class Rupiah
{
    /**
     * 1500000 → "1.500.000"
     */
    public static function format(?int $amount): string
    {
        if ($amount === null) {
            return '';
        }

        return number_format($amount, 0, ',', '.');
    }

    /**
     * 1500000 → "Rp 1.500.000"
     */
    public static function formatWithSymbol(?int $amount): string
    {
        if ($amount === null) {
            return '';
        }

        return 'Rp '.self::format($amount);
    }

    /**
     * "Rp 1.500.000" → 1500000. String tanpa digit menghasilkan null.
     */
    public static function parse(int|string|null $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $digits = preg_replace('/\D/', '', $value) ?? '';

        return $digits === '' ? null : (int) $digits;
    }
}
