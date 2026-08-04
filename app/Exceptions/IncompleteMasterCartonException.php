<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Product;
use InvalidArgumentException;

/**
 * Dilempar saat Container Calculator dipanggil untuk produk yang berat atau
 * volume master carton-nya belum diisi. Perhitungan tidak boleh menebak angka
 * karena hasilnya dipakai buyer sebagai estimasi muatan.
 *
 * @see docs/06-architecture.md bagian 5.5
 */
final class IncompleteMasterCartonException extends InvalidArgumentException
{
    public function __construct(public readonly Product $product)
    {
        parent::__construct(sprintf(
            'Produk %s belum memiliki data master carton yang lengkap.',
            $product->code,
        ));
    }
}
