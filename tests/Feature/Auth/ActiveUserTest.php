<?php

declare(strict_types=1);

use App\Models\User;

test('tamu tidak bisa mengakses dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('tamu tidak bisa mengakses pengaturan', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['admin.profile.edit', 'admin.security.edit']);

test('pengguna nonaktif ditolak saat login', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect()->assertSessionHasErrors('email');
    $this->assertGuest();

    $errors = $response->getSession()->get('errors');
    $message = is_array($errors)
        ? ($errors['email'][0] ?? '')
        : $errors->getBag('default')->first('email');

    expect($message)->toContain('Akun Anda dinonaktifkan');
});

test('pengguna aktif tetap bisa login', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('sesi berjalan langsung diputus saat akun dinonaktifkan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

    $user->forceFill(['is_active' => false])->saveQuietly();

    $this->actingAs($user->fresh())
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('kredensial salah tidak membocorkan status akun', function () {
    $user = User::factory()->inactive()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'kata-sandi-salah',
    ]);

    $errors = $response->getSession()->get('errors');
    $message = is_array($errors)
        ? ($errors['email'][0] ?? '')
        : $errors->getBag('default')->first('email');

    expect($message)->not->toContain('dinonaktifkan');
});
