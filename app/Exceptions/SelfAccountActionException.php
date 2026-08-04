<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat pengguna mencoba menonaktifkan atau menghapus akunnya sendiri.
 *
 * @see docs/09-tasks.md bagian 4.6
 */
final class SelfAccountActionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Akun sendiri tidak dapat dinonaktifkan atau dihapus. Minta admin lain melakukannya bila memang diperlukan.');
    }
}
