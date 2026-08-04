<?php

declare(strict_types=1);

namespace App\Enums;

enum PageBlockType: string
{
    case RichText = 'rich_text';
    case TextImage = 'text_image';
    case ListItems = 'list';
    case Stat = 'stat';

    public function label(): string
    {
        return match ($this) {
            self::RichText => 'Teks Kaya',
            self::TextImage => 'Teks & Gambar',
            self::ListItems => 'Daftar',
            self::Stat => 'Statistik',
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
