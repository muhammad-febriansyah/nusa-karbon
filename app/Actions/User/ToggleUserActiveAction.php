<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Exceptions\LastActiveAdminException;
use App\Exceptions\SelfAccountActionException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

final readonly class ToggleUserActiveAction
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * @throws SelfAccountActionException bila menonaktifkan akun sendiri
     * @throws LastActiveAdminException bila menyisakan panel tanpa admin aktif
     */
    public function handle(User $user, bool $active): User
    {
        if (! $active) {
            $this->guard($user);
        }

        // `is_active` tidak masuk daftar fillable `User`, jadi mass
        // assignment akan mengabaikannya tanpa error.
        $user->forceFill(['is_active' => $active])->save();

        return $user->refresh();
    }

    private function guard(User $user): void
    {
        if (Auth::id() === $user->id) {
            throw new SelfAccountActionException;
        }

        if ($user->is_active && $this->users->countActiveAdmins() <= 1) {
            throw new LastActiveAdminException;
        }
    }
}
