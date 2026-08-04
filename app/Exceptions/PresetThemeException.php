<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat preset bawaan hendak diubah atau dihapus. Preset adalah titik
 * aman untuk kembali bila kustomisasi merusak tampilan.
 *
 * @see docs/10-theming.md bagian 5
 */
final class PresetThemeException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Preset bawaan tidak dapat diubah atau dihapus. Duplikat dulu, lalu ubah salinannya.');
    }
}
