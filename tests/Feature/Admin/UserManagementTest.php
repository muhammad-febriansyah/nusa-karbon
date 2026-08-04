<?php

declare(strict_types=1);

use App\Models\RfqNote;
use App\Models\RfqRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('tamu tidak bisa mengakses manajemen pengguna', function () {
    $this->get(route('admin.users.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat pengguna', function () {
    User::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/index')
            ->has('users.data', 3)
            ->where('currentUserId', $this->admin->id),
        );
});

test('pengguna baru dibuat dengan role admin dan menerima undangan', function () {
    Notification::fake();

    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Rina Kusuma',
            'email' => 'rina@nusacarbon.com',
            'phone' => '+6281234567890',
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'rina@nusacarbon.com')->firstOrFail();

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->is_active)->toBeTrue();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('email pengguna wajib unik', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.users.store'), [
            'name' => 'Duplikat',
            'email' => $this->admin->email,
        ])
        ->assertSessionHasErrors('email');
});

test('avatar lama dihapus saat diganti', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    Storage::disk('public')->put('avatars/lama.jpg', 'x');
    $user->forceFill(['avatar_path' => 'avatars/lama.jpg'])->save();

    $this->actingAs($this->admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('baru.jpg', 500, 500),
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    expect($user->refresh()->avatar_path)->not->toBe('avatars/lama.jpg')
        ->and(Storage::disk('public')->exists('avatars/lama.jpg'))->toBeFalse();
});

test('pengguna tidak bisa menonaktifkan atau menghapus dirinya sendiri', function () {
    $other = User::factory()->create();
    $other->assignRole('admin');

    $this->actingAs($this->admin)
        ->put(route('admin.users.active', $this->admin), ['is_active' => false])
        ->assertSessionHasErrors('user');

    expect($this->admin->refresh()->is_active)->toBeTrue();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $this->admin))
        ->assertSessionHasErrors('user');

    expect(User::query()->whereKey($this->admin->id)->exists())->toBeTrue();
});

test('admin aktif terakhir tidak bisa dinonaktifkan maupun dihapus', function () {
    // Admin yang login di sini tanpa role admin, sehingga `$lastAdmin` benar
    // -benar satu-satunya admin aktif yang tersisa.
    $operator = User::factory()->create();
    $lastAdmin = $this->admin;

    $this->actingAs($operator)
        ->put(route('admin.users.active', $lastAdmin), ['is_active' => false])
        ->assertSessionHasErrors('user');

    expect($lastAdmin->refresh()->is_active)->toBeTrue();

    $this->actingAs($operator)
        ->delete(route('admin.users.destroy', $lastAdmin))
        ->assertSessionHasErrors('user');

    expect(User::query()->whereKey($lastAdmin->id)->exists())->toBeTrue();
});

test('pengguna lain dapat dinonaktifkan dan dihapus', function () {
    $other = User::factory()->create();
    $other->assignRole('admin');

    $this->actingAs($this->admin)
        ->put(route('admin.users.active', $other), ['is_active' => false])
        ->assertSessionHasNoErrors();

    expect($other->refresh()->is_active)->toBeFalse();

    $this->actingAs($this->admin)
        ->delete(route('admin.users.destroy', $other))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasNoErrors();

    expect(User::query()->whereKey($other->id)->exists())->toBeFalse()
        ->and(User::withTrashed()->whereKey($other->id)->exists())->toBeTrue();
});

test('halaman detail memuat statistik dan aktivitas pengguna', function () {
    $this->actingAs($this->admin);

    $rfq = RfqRequest::factory()->create();
    RfqNote::factory()->create([
        'rfq_request_id' => $rfq->id,
        'user_id' => $this->admin->id,
    ]);

    $this->admin->update(['name' => 'Nama Diperbarui']);

    $this->get(route('admin.users.show', $this->admin))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/show')
            ->where('handledRfqs', 1)
            ->has('activities'),
        );
});

test('tautan pembuatan password dapat dikirim ulang', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.users.password-link', $user))
        ->assertRedirect();

    Notification::assertSentTo($user, ResetPassword::class);
});
