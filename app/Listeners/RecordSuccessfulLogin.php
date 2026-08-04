<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

/**
 * Mencatat waktu dan IP login terakhir.
 *
 * Disimpan diam-diam: tanpa memicu event model agar tidak membanjiri
 * `activity_log`, dan tanpa menyentuh `updated_at` agar kolom itu tetap
 * mencerminkan perubahan data profil, bukan aktivitas login.
 */
final class RecordSuccessfulLogin
{
    public function __construct(private readonly Request $request) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $user->timestamps = false;

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $this->request->ip(),
        ])->saveQuietly();

        $user->timestamps = true;
    }
}
