<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\User;

use App\Models\User;

/**
 * Sengaja tanpa field password: penggantian password hanya lewat tautan reset
 * yang dikirim ke pemilik akun.
 */
final class UpdateUserRequest extends StoreUserRequest
{
    protected function currentUserId(): ?int
    {
        $user = $this->route('user');

        return $user instanceof User ? $user->id : null;
    }
}
