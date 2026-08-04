<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AvatarService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Membuat pengguna baru lalu mengirim undangan pembuatan password.
 *
 * Password acak dipasang supaya kolomnya tidak kosong; pengguna tetap harus
 * memakai tautan undangan untuk menentukan miliknya sendiri.
 *
 * @see docs/09-tasks.md bagian 4.6
 */
final readonly class CreateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AvatarService $avatars,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $avatar = $data['avatar'] ?? null;
            $isActive = (bool) ($data['is_active'] ?? true);

            unset($data['avatar'], $data['remove_avatar'], $data['is_active']);

            $data['password'] = Str::password(32);

            $user = $this->users->create($data);

            // `is_active` di luar daftar fillable `User`, jadi diisi eksplisit.
            $user->forceFill(['is_active' => $isActive])->save();

            if ($avatar instanceof UploadedFile) {
                $user->forceFill(['avatar_path' => $this->avatars->store($user, $avatar)])->save();
            }

            $user->assignRole('admin');

            return $user;
        });

        // Undangan memakai alur reset password bawaan Fortify, jadi tautannya
        // sekali pakai dan kedaluwarsa sesuai config.
        Password::sendResetLink(['email' => $user->email]);

        return $user;
    }
}
