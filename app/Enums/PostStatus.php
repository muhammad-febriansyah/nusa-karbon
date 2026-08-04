<?php

declare(strict_types=1);

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Scheduled => 'Terjadwal',
            self::Published => 'Terbit',
            self::Archived => 'Arsip',
        };
    }

    /**
     * Token warna badge. Lihat docs/05-design-system.md bagian 8.
     */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'muted',
            self::Scheduled => 'info',
            self::Published => 'success',
            self::Archived => 'neutral',
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
