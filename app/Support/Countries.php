<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Daftar negara ISO 3166-1 alpha-2 untuk isian tujuan pengiriman.
 *
 * Hanya kodenya yang disimpan di sini; namanya diambil dari ICU lewat
 * `ext-intl`, jadi satu daftar ini melayani seluruh locale yang direncanakan di
 * `config/locales.php` tanpa perlu menerjemahkan 250 nama negara empat kali.
 *
 * Bila `ext-intl` tidak terpasang, namanya jatuh kembali ke kodenya sendiri.
 * Hasilnya memang lebih miskin, tetapi tidak pernah lebih buruk daripada isian
 * teks bebas yang digantikan daftar ini — pembeli tetap memilih "AE", bukan
 * mengetiknya dan salah.
 */
final class Countries
{
    /**
     * Kode yang sengaja tidak ditampilkan.
     *
     * ICU juga mengenal kode usang (`SU` untuk Uni Soviet, `YU` untuk
     * Yugoslavia), pengelompokan (`EU`, `EZ`), wilayah khusus di luar ISO
     * 3166-1 (`AC`, `TA`, `DG`), dan kode semu untuk pengujian (`XA`, `XB`).
     * Semuanya menghasilkan nama yang tampak sah di daftar pilihan padahal
     * tidak pernah bisa jadi tujuan pengiriman yang benar.
     *
     * @var array<int, string>
     */
    private const EXCLUDED = [
        'AC', 'AN', 'BU', 'CP', 'CQ', 'CS', 'DD', 'DG', 'DY', 'EA',
        'EU', 'EZ', 'FX', 'HV', 'IC', 'NH', 'QO', 'RH', 'SU', 'TA',
        'TP', 'UK', 'UN', 'VD', 'XA', 'XB', 'YD', 'YU', 'ZR', 'ZZ',
    ];

    /**
     * Seluruh kode negara yang diterima, urut abjad.
     *
     * @return array<int, string>
     */
    public static function codes(): array
    {
        static $codes = null;

        if ($codes !== null) {
            return $codes;
        }

        $found = [];

        foreach (range('A', 'Z') as $first) {
            foreach (range('A', 'Z') as $second) {
                $code = $first.$second;

                if (in_array($code, self::EXCLUDED, true)) {
                    continue;
                }

                // ICU mengembalikan kodenya sendiri untuk kombinasi yang tidak
                // dikenal — itulah penanda bahwa kodenya tidak terpakai.
                if (self::displayName($code, 'en') !== $code) {
                    $found[] = $code;
                }
            }
        }

        return $codes = $found;
    }

    /**
     * Pilihan untuk daftar dropdown, urut menurut nama dalam locale terkait.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(string $locale): array
    {
        $names = [];

        foreach (self::codes() as $code) {
            $names[$code] = self::displayName($code, $locale);
        }

        /*
         * Diurutkan dengan `Collator` supaya huruf beraksen mendarat di tempat
         * yang diharapkan pembacanya — "Åland" duduk di dekat "Afganistan"
         * dalam bahasa Indonesia, bukan terlempar ke ujung daftar seperti pada
         * pengurutan berdasarkan titik kode.
         */
        if (class_exists(\Collator::class)) {
            $collator = new \Collator($locale);
            $collator->asort($names);
        } else {
            asort($names);
        }

        $options = [];

        foreach ($names as $code => $name) {
            $options[] = ['value' => $code, 'label' => $name];
        }

        return $options;
    }

    private static function displayName(string $code, string $locale): string
    {
        if (! class_exists(\Locale::class)) {
            return $code;
        }

        // ICU mengembalikan `false` bila data locale-nya gagal dimuat; kodenya
        // sendiri tetap keterangan yang benar, hanya lebih miskin.
        $name = \Locale::getDisplayRegion('-'.$code, $locale);

        return $name === false || $name === '' ? $code : $name;
    }
}
