<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case Coa = 'coa';
    case Msds = 'msds';
    case Sht = 'sht';
    case PackingList = 'packing_list';
    case Other = 'other';

    /**
     * COA, MSDS, dan SHT adalah istilah perdagangan yang tidak diterjemahkan.
     *
     * @see docs/CLAUDE.md bagian "Multi-bahasa"
     */
    public function label(): string
    {
        return match ($this) {
            self::Coa => 'COA',
            self::Msds => 'MSDS',
            self::Sht => 'SHT',
            self::PackingList => 'Packing List',
            self::Other => 'Lainnya',
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
