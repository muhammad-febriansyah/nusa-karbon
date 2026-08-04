<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AvatarService;
use Illuminate\Http\UploadedFile;

final readonly class UpdateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private AvatarService $avatars,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $avatar = $data['avatar'] ?? null;
        $removeAvatar = (bool) ($data['remove_avatar'] ?? false);
        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : null;

        unset($data['avatar'], $data['remove_avatar'], $data['password'], $data['is_active']);

        // `AvatarService::store()` sudah menghapus berkas lama sebelum menulis
        // yang baru, jadi tidak ada berkas yatim tertinggal.
        if ($avatar instanceof UploadedFile) {
            $data['avatar_path'] = $this->avatars->store($user, $avatar);
        } elseif ($removeAvatar) {
            $this->avatars->delete($user);
            $data['avatar_path'] = null;
        }

        $user = $this->users->update($user, $data);

        // `is_active` di luar daftar fillable `User`, jadi diisi eksplisit.
        if ($isActive !== null) {
            $user->forceFill(['is_active' => $isActive])->save();
        }

        return $user;
    }
}
