<?php

declare(strict_types=1);

use App\Models\Representative;
use App\Models\User;
use Database\Seeders\RepresentativeSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/**
 * @return array<string, mixed>
 */
function representativePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Adi Wicaksono',
        'position' => ['id' => 'Export Sales Manager'],
        'region' => 'MENA',
        'email' => 'adi@nusacarbon.com',
        'phone' => '+622112345678',
        'whatsapp' => '+6281234567890',
        'started_at' => '2026-01-05',
        'is_active' => true,
    ], $overrides);
}

test('tamu tidak bisa mengakses perwakilan', function () {
    $this->get(route('admin.representatives.index'))
        ->assertRedirect(route('login'));
});

test('halaman index tampil dengan daftar perwakilan', function () {
    Representative::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get(route('admin.representatives.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/representatives/index')
            ->has('representatives.data', 3)
            ->has('representatives.meta')
            ->has('filters'),
        );
});

test('perwakilan baru dapat dibuat', function () {
    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload())
        ->assertRedirect(route('admin.representatives.index'))
        ->assertSessionHasNoErrors();

    $representative = Representative::query()->first();

    expect($representative)->not->toBeNull()
        ->and($representative->name)->toBe('Adi Wicaksono')
        ->and($representative->getTranslation('position', 'id'))->toBe('Export Sales Manager')
        ->and($representative->email)->toBe('adi@nusacarbon.com')
        ->and($representative->region)->toBe('MENA');
});

test('email domain gratisan ditolak dengan peringatan tegas', function (string $email) {
    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload([
            'email' => $email,
        ]))
        ->assertSessionHasErrors(['email' => 'Perwakilan resmi wajib memakai email domain perusahaan. Alamat gratisan seperti Gmail atau Yahoo justru dipakai penipu untuk menyamar sebagai perwakilan.']);

    expect(Representative::query()->count())->toBe(0);
})->with([
    'gmail' => 'sales@gmail.com',
    'yahoo' => 'sales@yahoo.com',
    'outlook' => 'sales@outlook.com',
    'proton' => 'sales@proton.me',
    'huruf kapital' => 'Sales@GMAIL.com',
]);

test('email disimpan dalam huruf kecil', function () {
    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload([
            'email' => '  ADI@Nusacarbon.COM ',
        ]))
        ->assertSessionHasNoErrors();

    expect(Representative::query()->first()->email)->toBe('adi@nusacarbon.com');
});

test('email perwakilan wajib unik', function () {
    Representative::factory()->create(['email' => 'adi@nusacarbon.com']);

    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload())
        ->assertSessionHasErrors('email');

    expect(Representative::query()->count())->toBe(1);
});

test('tanggal berakhir tidak boleh mendahului tanggal mulai', function () {
    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload([
            'started_at' => '2026-05-01',
            'ended_at' => '2026-04-30',
        ]))
        ->assertSessionHasErrors('ended_at');
});

test('perwakilan dapat diperbarui', function () {
    $representative = Representative::factory()->create([
        'email' => 'lama@nusacarbon.com',
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.representatives.update', $representative), representativePayload([
            'email' => 'lama@nusacarbon.com',
            'name' => 'Nama Baru',
            'is_active' => false,
        ]))
        ->assertRedirect(route('admin.representatives.index'))
        ->assertSessionHasNoErrors();

    $representative->refresh();

    expect($representative->name)->toBe('Nama Baru')
        ->and($representative->is_active)->toBeFalse();
});

test('perwakilan dihapus secara soft delete', function () {
    $representative = Representative::factory()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.representatives.destroy', $representative))
        ->assertRedirect();

    expect(Representative::query()->count())->toBe(0)
        ->and(Representative::withTrashed()->count())->toBe(1);
});

test('foto lama dihapus saat diganti', function () {
    Storage::fake('public');
    Storage::disk('public')->put('representatives/lama.jpg', 'x');

    $representative = Representative::factory()->create([
        'email' => 'adi@nusacarbon.com',
        'photo_path' => 'representatives/lama.jpg',
    ]);

    $this->actingAs($this->user)
        ->put(route('admin.representatives.update', $representative), representativePayload([
            'remove_photo' => true,
        ]))
        ->assertSessionHasNoErrors();

    expect($representative->refresh()->photo_path)->toBeNull()
        ->and(Storage::disk('public')->exists('representatives/lama.jpg'))->toBeFalse();
});

test('setiap create update dan delete tercatat di activity log', function () {
    $this->actingAs($this->user)
        ->post(route('admin.representatives.store'), representativePayload())
        ->assertSessionHasNoErrors();

    $representative = Representative::query()->firstOrFail();

    $this->actingAs($this->user)
        ->put(route('admin.representatives.update', $representative), representativePayload([
            'name' => 'Nama Diperbarui',
        ]))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->user)
        ->delete(route('admin.representatives.destroy', $representative));

    $events = Activity::query()
        ->where('log_name', 'representative')
        ->orderBy('id')
        ->pluck('event')
        ->all();

    expect($events)->toBe(['created', 'updated', 'deleted']);

    // Nilai lama dan baru tersimpan di `attribute_changes` sejak activitylog v5.
    $updated = Activity::query()
        ->where('log_name', 'representative')
        ->where('event', 'updated')
        ->firstOrFail();

    expect($updated->causer_id)->toBe($this->user->id)
        ->and($updated->attribute_changes['old']['name'])->toBe('Adi Wicaksono')
        ->and($updated->attribute_changes['attributes']['name'])->toBe('Nama Diperbarui');
});

test('filter wilayah dan status menyaring daftar', function () {
    Representative::factory()->create(['region' => 'MENA', 'email' => 'a@nusacarbon.com']);
    Representative::factory()->create(['region' => 'Europe', 'email' => 'b@nusacarbon.com']);
    Representative::factory()->inactive()->create(['region' => 'MENA', 'email' => 'c@nusacarbon.com']);

    $this->actingAs($this->user)
        ->get(route('admin.representatives.index', ['region' => 'MENA']))
        ->assertInertia(fn (Assert $page) => $page->has('representatives.data', 2));

    $this->actingAs($this->user)
        ->get(route('admin.representatives.index', ['region' => 'MENA', 'is_active' => '1']))
        ->assertInertia(fn (Assert $page) => $page->has('representatives.data', 1));
});

test('seeder mengisi perwakilan awal tanpa duplikat', function () {
    $this->seed(RepresentativeSeeder::class);
    $this->seed(RepresentativeSeeder::class);

    expect(Representative::query()->count())->toBe(2);
});
