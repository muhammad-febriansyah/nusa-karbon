<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan foto profil ke disk `public` dan membereskan file lama.
 *
 * Gambar sudah dipotong 1:1 di sisi klien sebelum diunggah, jadi service ini
 * hanya mengurus penyimpanan dan pembersihan.
 */
final class AvatarService
{
    private const DIRECTORY = 'avatars';

    public function store(User $user, UploadedFile $file): string
    {
        $this->delete($user);

        $name = Str::lower(Str::random(24)).'.'.$file->extension();

        $path = $file->storeAs(self::DIRECTORY, $name, 'public');

        return $path === false ? '' : $path;
    }

    public function delete(User $user): void
    {
        if ($user->avatar_path === null) {
            return;
        }

        Storage::disk('public')->delete($user->avatar_path);
    }
}
