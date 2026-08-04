<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Pembantu locale website publik. Nilainya dibaca dari `config/locales.php`.
 *
 * @see docs/08-multilingual.md
 */
final class Locale
{
    public static function default(): string
    {
        return (string) config('locales.default', 'id');
    }

    /**
     * @return array<int, string>
     */
    public static function supported(): array
    {
        /** @var array<string, array{name: string, native: string, dir: string, flag: string}> $supported */
        $supported = config('locales.supported', []);

        return array_keys($supported);
    }

    /**
     * Bahasa yang sudah diumumkan tetapi rutenya belum ada.
     *
     * Terpisah dari `supported()` dengan sengaja: apa pun yang masuk daftar
     * dukungan akan ikut dipakai form admin, validasi, dan penyusun hreflang.
     * Daftar ini hanya untuk ditampilkan.
     *
     * @return array<string, array{name: string, native: string, dir: string, flag: string}>
     */
    public static function planned(): array
    {
        /** @var array<string, array{name: string, native: string, dir: string, flag: string}> $planned */
        $planned = config('locales.planned', []);

        return $planned;
    }

    public static function isSupported(string $locale): bool
    {
        return in_array($locale, self::supported(), true);
    }

    /**
     * @return array{name: string, native: string, dir: string, flag: string}|null
     */
    public static function meta(string $locale): ?array
    {
        /** @var array<string, array{name: string, native: string, dir: string, flag: string}> $supported */
        $supported = config('locales.supported', []);

        return $supported[$locale] ?? null;
    }

    /**
     * Arah teks: `ltr` atau `rtl`. Locale tak dikenal dianggap `ltr`.
     */
    public static function direction(?string $locale = null): string
    {
        $meta = self::meta($locale ?? app()->getLocale());

        return $meta['dir'] ?? 'ltr';
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::direction($locale) === 'rtl';
    }

    /**
     * Prefix URL untuk locale. Bahasa default tidak memakai prefix.
     */
    public static function urlPrefix(string $locale): string
    {
        return $locale === self::default() ? '' : $locale;
    }
}
