<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Incoterms tidak pernah diterjemahkan, termasuk pada locale Arab dan Rusia.
 *
 * @see docs/CLAUDE.md bagian "Multi-bahasa"
 */
enum Incoterm: string
{
    case Fob = 'FOB';
    case Cif = 'CIF';
    case Cfr = 'CFR';
    case Exw = 'EXW';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::Other => 'Lainnya',
            default => $this->value,
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
