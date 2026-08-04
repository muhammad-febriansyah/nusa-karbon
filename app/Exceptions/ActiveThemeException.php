<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat tema yang sedang aktif hendak dihapus — menghapusnya akan
 * membuat panel atau halaman publik kehilangan paletnya.
 */
final class ActiveThemeException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Tema ini sedang aktif. Aktifkan tema lain lebih dulu sebelum menghapusnya.');
    }
}
