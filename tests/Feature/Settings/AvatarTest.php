<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('foto profil dapat diunggah', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('foto.jpg', 800, 800),
        ])
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('mengganti foto menghapus file lama', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('admin.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('lama.jpg', 800, 800),
    ]);

    $old = $user->refresh()->avatar_path;

    $this->actingAs($user)->patch(route('admin.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('baru.jpg', 800, 800),
    ]);

    $new = $user->refresh()->avatar_path;

    expect($new)->not->toBe($old);
    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($new);
});

test('foto profil dapat dihapus', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch(route('admin.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('foto.jpg', 800, 800),
    ]);

    $path = $user->refresh()->avatar_path;

    $this->actingAs($user)
        ->delete(route('admin.profile.avatar.destroy'))
        ->assertRedirect(route('admin.profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('gambar di bawah 400x400 ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('kecil.jpg', 200, 200),
        ])
        ->assertSessionHasErrors('avatar');

    expect($user->refresh()->avatar_path)->toBeNull();
});

test('file bukan gambar ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('avatar');
});

test('url foto profil ikut dikirim ke frontend', function () {
    $user = User::factory()->create();

    expect($user->avatar)->toBeNull();

    $this->actingAs($user)->patch(route('admin.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('foto.jpg', 800, 800),
    ]);

    expect($user->refresh()->avatar)->toContain('/storage/avatars/');
});

test('nomor telepon dapat disimpan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '+62 812 3456 7890',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->phone)->toBe('+62 812 3456 7890');
});

test('nomor telepon dengan huruf ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->patch(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => 'hubungi saya',
        ])
        ->assertSessionHasErrors('phone');
});
