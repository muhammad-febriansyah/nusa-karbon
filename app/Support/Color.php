<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Perhitungan kontras warna sesuai rumus WCAG 2.1.
 *
 * Dipakai validasi tema: palet yang tidak terbaca ditolak sebelum tersimpan,
 * bukan setelah admin melihat panelnya rusak.
 *
 * @see docs/10-theming.md bagian 4
 */
final class Color
{
    /**
     * Luminansi relatif 0–1; 0 hitam pekat, 1 putih.
     */
    public static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = self::toRgb($hex);

        $channel = static fn (float $value): float => ($value /= 255) <= 0.03928
            ? $value / 12.92
            : (($value + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    /**
     * Rasio kontras 1–21. WCAG mensyaratkan minimal 4,5 untuk teks normal.
     */
    public static function contrastRatio(string $first, string $second): float
    {
        $a = self::relativeLuminance($first);
        $b = self::relativeLuminance($second);

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

    /**
     * Hitam atau putih — mana yang paling kontras terhadap latar.
     */
    public static function bestForeground(string $background): string
    {
        return self::contrastRatio($background, '#FFFFFF') >= self::contrastRatio($background, '#111111')
            ? '#FFFFFF'
            : '#111111';
    }

    public static function isHex(string $value): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1;
    }

    /**
     * @return array<int, float>
     */
    private static function toRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (float) hexdec(substr($hex, 0, 2)),
            (float) hexdec(substr($hex, 2, 2)),
            (float) hexdec(substr($hex, 4, 2)),
        ];
    }
}
