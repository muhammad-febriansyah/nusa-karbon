<?php

declare(strict_types=1);

use App\Support\Rupiah;

test('rupiah diformat dengan pemisah ribuan titik', function () {
    expect(Rupiah::format(1500000))->toBe('1.500.000')
        ->and(Rupiah::format(0))->toBe('0')
        ->and(Rupiah::formatWithSymbol(1500000))->toBe('Rp 1.500.000')
        ->and(Rupiah::format(null))->toBe('')
        ->and(Rupiah::formatWithSymbol(null))->toBe('');
});

test('rupiah diurai kembali menjadi integer murni', function () {
    expect(Rupiah::parse('Rp 1.500.000'))->toBe(1500000)
        ->and(Rupiah::parse('1500000'))->toBe(1500000)
        ->and(Rupiah::parse(1500000))->toBe(1500000)
        ->and(Rupiah::parse(''))->toBeNull()
        ->and(Rupiah::parse('Rp'))->toBeNull()
        ->and(Rupiah::parse(null))->toBeNull();
});
