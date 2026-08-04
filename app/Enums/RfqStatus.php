<?php

declare(strict_types=1);

namespace App\Enums;

enum RfqStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Quoted = 'quoted';
    case Negotiating = 'negotiating';
    case Won = 'won';
    case Lost = 'lost';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::Contacted => 'Dihubungi',
            self::Quoted => 'Penawaran Dikirim',
            self::Negotiating => 'Negosiasi',
            self::Won => 'Menang',
            self::Lost => 'Kalah',
            self::Spam => 'Spam',
        };
    }

    /**
     * Token warna badge. Lihat docs/05-design-system.md bagian 8.
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'primary',
            self::Contacted => 'info',
            self::Quoted, self::Negotiating => 'warning',
            self::Won => 'success',
            self::Lost => 'neutral',
            self::Spam => 'destructive',
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
