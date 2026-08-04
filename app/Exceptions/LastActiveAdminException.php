<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat aksi akan menyisakan panel admin tanpa satu pun admin aktif —
 * kondisi yang mengunci semua orang di luar sistem.
 *
 * @see docs/09-tasks.md bagian 4.6
 */
final class LastActiveAdminException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Ini satu-satunya admin aktif yang tersisa. Aktifkan admin lain lebih dulu sebelum menonaktifkan atau menghapus akun ini.');
    }
}
