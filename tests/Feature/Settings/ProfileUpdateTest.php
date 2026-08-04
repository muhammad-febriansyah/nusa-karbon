<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

/*
 * Test "user can delete their account" dan "correct password must be provided
 * to delete account" dihapus bersama fiturnya: pengguna tidak boleh menghapus
 * dirinya sendiri. Penjagaan penghapusan pengguna diuji ulang di modul
 * Manajemen Pengguna.
 *
 * @see docs/CLAUDE.md bagian "Pengguna & Role"
 */
test('pengguna tidak bisa menghapus akunnya sendiri', function () {
    $user = User::factory()->create();

    // 405: URL-nya masih ada untuk GET/PATCH, tapi DELETE tidak lagi terdaftar.
    $this->actingAs($user)
        ->delete('/admin/settings/profile')
        ->assertMethodNotAllowed();

    expect(User::query()->whereKey($user->id)->exists())->toBeTrue();
});
