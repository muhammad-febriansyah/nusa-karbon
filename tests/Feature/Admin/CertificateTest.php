<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\User;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Database\Seeders\CertificateSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses sertifikat', function () {
    $this->get(route('admin.certificates.index'))
        ->assertRedirect(route('login'));
});

test('halaman index tampil dengan daftar sertifikat', function () {
    Certificate::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.certificates.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/certificates/index')
            ->has('certificates.data', 3)
            ->has('certificates.meta')
            ->has('expiringCount')
            ->has('filters'),
        );
});

test('sertifikat baru dapat dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.certificates.store'), [
            'name' => ['id' => 'ISO 9001:2015'],
            'description' => ['id' => 'Sistem manajemen mutu.'],
            'issuer' => 'TUV Rheinland',
            'certificate_number' => '01 100 1234567',
            'issued_at' => '2026-01-10',
            'expires_at' => '2029-01-09',
            'show_in_trust_bar' => true,
            'is_active' => true,
        ])
        ->assertRedirect(route('admin.certificates.index'))
        ->assertSessionHasNoErrors();

    $certificate = Certificate::query()->first();

    expect($certificate)->not->toBeNull()
        ->and($certificate->getTranslation('name', 'id'))->toBe('ISO 9001:2015')
        ->and($certificate->issuer)->toBe('TUV Rheinland')
        ->and($certificate->show_in_trust_bar)->toBeTrue()
        ->and($certificate->issued_at?->toDateString())->toBe('2026-01-10')
        ->and($certificate->expires_at?->toDateString())->toBe('2029-01-09');
});

test('nama sertifikat wajib diisi', function () {
    $this->actingAs($this->user)
        ->post(route('admin.certificates.store'), [
            'name' => ['id' => ''],
        ])
        ->assertSessionHasErrors('name.id');

    expect(Certificate::query()->count())->toBe(0);
});

test('tanggal kedaluwarsa tidak boleh mendahului tanggal terbit', function () {
    $this->actingAs($this->user)
        ->post(route('admin.certificates.store'), [
            'name' => ['id' => 'Sertifikat Uji'],
            'issued_at' => '2026-05-01',
            'expires_at' => '2026-04-30',
        ])
        ->assertSessionHasErrors('expires_at');
});

test('sertifikat dapat diperbarui', function () {
    $certificate = Certificate::factory()->create(['issuer' => 'Lama']);

    $this->actingAs($this->user)
        ->put(route('admin.certificates.update', $certificate), [
            'name' => ['id' => 'Nama Baru'],
            'issuer' => 'SGS Indonesia',
            'show_in_trust_bar' => true,
            'is_active' => false,
        ])
        ->assertRedirect(route('admin.certificates.index'))
        ->assertSessionHasNoErrors();

    $certificate->refresh();

    expect($certificate->getTranslation('name', 'id'))->toBe('Nama Baru')
        ->and($certificate->issuer)->toBe('SGS Indonesia')
        ->and($certificate->show_in_trust_bar)->toBeTrue()
        ->and($certificate->is_active)->toBeFalse();
});

test('sertifikat dihapus secara soft delete', function () {
    $certificate = Certificate::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.certificates.destroy', $certificate))
        ->assertRedirect();

    expect(Certificate::query()->count())->toBe(0)
        ->and(Certificate::withTrashed()->count())->toBe(1);
});

test('berkas lama dihapus saat logo diganti', function () {
    Storage::fake('public');
    Storage::disk('public')->put('certificates/logos/lama.png', 'x');

    $certificate = Certificate::factory()->create([
        'logo_path' => 'certificates/logos/lama.png',
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.certificates.update', $certificate), [
            'name' => ['id' => 'Nama'],
            'remove_logo' => true,
        ])
        ->assertSessionHasNoErrors();

    expect($certificate->refresh()->logo_path)->toBeNull()
        ->and(Storage::disk('public')->exists('certificates/logos/lama.png'))->toBeFalse();
});

test('filter menyaring sertifikat yang akan kedaluwarsa', function () {
    Certificate::factory()->expiringSoon(20)->create(['issuer' => 'Segera']);
    Certificate::factory()->create(['issuer' => 'Aman']);
    Certificate::factory()->expired()->create(['issuer' => 'Lewat']);

    $this->actingAs($this->user)
        ->get(route('admin.certificates.index', ['expiry' => 'expiring']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('certificates.data', 1)
            ->where('certificates.data.0.issuer', 'Segera'),
        );

    $this->actingAs($this->user)
        ->get(route('admin.certificates.index', ['expiry' => 'expired']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('certificates.data', 1)
            ->where('certificates.data.0.issuer', 'Lewat'),
        );
});

test('peringatan kedaluwarsa hanya menghitung sertifikat aktif dalam 60 hari', function () {
    Certificate::factory()->expiringSoon(30)->create();
    Certificate::factory()->expiringSoon(59)->create();
    Certificate::factory()->expiringSoon(90)->create();
    Certificate::factory()->expiringSoon(10)->inactive()->create();
    Certificate::factory()->expired()->create();

    $certificates = app(CertificateRepositoryInterface::class);

    expect($certificates->expiringWithin()->count())->toBe(2);

    $this->actingAs($this->user)
        ->get(route('admin.certificates.index'))
        ->assertInertia(fn (Assert $page) => $page->where('expiringCount', 2));
});

test('perubahan sertifikat tercatat di activity log', function () {
    $certificate = Certificate::factory()->create(['issuer' => 'Lama']);

    $this->actingAs($this->user)
        ->put(route('admin.certificates.update', $certificate), [
            'name' => ['id' => 'Nama'],
            'issuer' => 'Baru',
        ])
        ->assertSessionHasNoErrors();

    // Sejak activitylog v5 nilai lama dan baru ada di `attribute_changes`.
    $activity = Activity::query()
        ->where('log_name', 'certificate')
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->user->id)
        ->and($activity->attribute_changes['old']['issuer'])->toBe('Lama')
        ->and($activity->attribute_changes['attributes']['issuer'])->toBe('Baru');
});

test('seeder mengisi sertifikat awal tanpa duplikat', function () {
    $this->seed(CertificateSeeder::class);
    $this->seed(CertificateSeeder::class);

    expect(Certificate::query()->count())->toBe(4)
        ->and(Certificate::query()->trustBar()->count())->toBe(2);
});
