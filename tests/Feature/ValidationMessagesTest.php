<?php

declare(strict_types=1);

use App\Models\User;

test('locale aplikasi memakai Bahasa Indonesia', function () {
    expect(app()->getLocale())->toBe('id');
});

test('kolom wajib memakai pesan Bahasa Indonesia dan label istilah pengguna', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), ['name' => '', 'email' => ''])
        ->assertSessionHasErrors([
            'name' => 'Kolom nama lengkap wajib diisi.',
            'email' => 'Kolom email wajib diisi.',
        ]);
});

test('format email salah memakai pesan Bahasa Indonesia', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => 'Adi Wicaksono',
            'email' => 'bukan-email',
        ])
        ->assertSessionHasErrors([
            'email' => 'Kolom email harus berupa alamat email yang valid.',
        ]);
});

test('email duplikat memberi tahu cara memperbaiki', function () {
    User::factory()->create(['email' => 'terpakai@nusacarbon.com']);

    $this->actingAs(User::factory()->create())
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => 'Adi Wicaksono',
            'email' => 'terpakai@nusacarbon.com',
        ])
        ->assertSessionHasErrors([
            'email' => 'email ini sudah dipakai. Gunakan yang lain.',
        ]);
});

test('kredensial salah memakai pesan Bahasa Indonesia', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'kata-sandi-salah',
        ])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
});

test('kata sandi saat ini yang salah memakai pesan Bahasa Indonesia', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('admin.security.edit'))
        ->put(route('admin.user-password.update'), [
            'current_password' => 'kata-sandi-salah',
            'password' => 'kata-sandi-baru-123',
            'password_confirmation' => 'kata-sandi-baru-123',
        ])
        ->assertSessionHasErrors([
            'current_password' => 'Kata sandi yang Anda masukkan salah.',
        ]);
});

test('konfirmasi kata sandi tidak cocok memakai pesan Bahasa Indonesia', function () {
    $this->actingAs(User::factory()->create())
        ->from(route('admin.security.edit'))
        ->put(route('admin.user-password.update'), [
            'current_password' => 'password',
            'password' => 'kata-sandi-baru-123',
            'password_confirmation' => 'beda-sendiri',
        ])
        ->assertSessionHasErrors([
            'password' => 'Konfirmasi kata sandi tidak cocok.',
        ]);
});

test('status reset kata sandi memakai Bahasa Indonesia', function () {
    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    expect(session('status'))->toBe('Tautan reset kata sandi sudah dikirim ke email Anda.');
});
