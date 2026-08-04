<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Menentukan komponen input yang dirender halaman pengaturan sekaligus cara
 * nilainya divalidasi.
 *
 * @see docs/04-erd.md bagian 3.7
 */
enum SettingType: string
{
    case Str = 'string';
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Json = 'json';
    case Image = 'image';
    case File = 'file';
    case EmailList = 'email_list';
    case Translatable = 'translatable';
    case Color = 'color';
    case Coordinate = 'coordinate';

    public function label(): string
    {
        return match ($this) {
            self::Str => 'Teks singkat',
            self::Text => 'Teks panjang',
            self::Number => 'Angka',
            self::Boolean => 'Ya / Tidak',
            self::Json => 'JSON',
            self::Image => 'Gambar',
            self::File => 'Berkas',
            self::EmailList => 'Daftar email',
            self::Translatable => 'Teks multi-bahasa',
            self::Color => 'Warna',
            self::Coordinate => 'Koordinat',
        };
    }

    /**
     * Aturan validasi dasar per tipe; aturan tambahan datang dari kolom
     * `rules` tiap baris pengaturan.
     *
     * @return array<int, string>
     */
    public function baseRules(): array
    {
        return match ($this) {
            self::Str, self::Color, self::File, self::Image => ['nullable', 'string', 'max:500'],
            self::Text => ['nullable', 'string', 'max:5000'],
            self::Number, self::Coordinate => ['nullable', 'numeric'],
            self::Boolean => ['nullable', 'boolean'],
            self::Json, self::Translatable => ['nullable', 'array'],
            self::EmailList => ['nullable', 'string', 'max:1000'],
        };
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases(),
        );
    }
}
