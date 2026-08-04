<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Exceptions\LastActiveAdminException;
use App\Exceptions\SelfAccountActionException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class DeleteUserAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * Soft delete: jejak pengguna di activity log dan catatan RFQ tetap bisa
     * ditelusuri setelah akunnya dicabut.
     *
     * @throws SelfAccountActionException bila menghapus akun sendiri
     * @throws LastActiveAdminException bila menyisakan panel tanpa admin aktif
     */
    public function handle(User $user): bool
    {
        if (Auth::id() === $user->id) {
            throw new SelfAccountActionException;
        }

        if ($user->is_active && $this->users->countActiveAdmins() <= 1) {
            throw new LastActiveAdminException;
        }

        return $this->users->delete($user);
    }
}
