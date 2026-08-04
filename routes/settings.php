<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('settings', '/admin/settings/profile');

        Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('settings/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    });

/*
 * Rute `profile.destroy` sengaja dihapus: pengguna tidak boleh menghapus atau
 * menonaktifkan dirinya sendiri. Penghapusan pengguna dipindahkan ke menu
 * Manajemen Pengguna, lengkap dengan penjagaan "bukan diri sendiri" dan
 * "admin aktif terakhir tidak bisa dihapus".
 *
 * @see docs/CLAUDE.md bagian "Pengguna & Role"
 */

Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('settings/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::class)
            ->name('security.edit');

        Route::put('settings/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('user-password.update');
    });

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => route('admin.security.edit'),
        'manage' => route('admin.security.edit'),
    ]);
})->name('well-known.passkeys');
