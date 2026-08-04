<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Representative;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('tamu tidak bisa mengakses log aktivitas', function () {
    $this->get(route('admin.activity-log.index'))
        ->assertRedirect(route('login'));
});

test('halaman index memuat aktivitas beserta filternya', function () {
    $this->actingAs($this->user);

    Certificate::factory()->create();
    Representative::factory()->create();

    $this->get(route('admin.activity-log.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/activity-log/index')
            // Pembuatan pengguna di beforeEach juga tercatat.
            ->has('activities.data', 3)
            ->has('subjectTypes', 3)
            ->has('users'),
        );
});

test('filter tipe objek dan pengguna menyaring daftar', function () {
    $this->actingAs($this->user);

    Certificate::factory()->create();
    Representative::factory()->create();

    $this->get(route('admin.activity-log.index', [
        'subject_type' => Certificate::class,
    ]))
        ->assertInertia(fn (Assert $page) => $page->has('activities.data', 1));

    $this->get(route('admin.activity-log.index', [
        'causer_id' => $this->user->id,
    ]))
        ->assertInertia(fn (Assert $page) => $page->has('activities.data', 2));

    $other = User::factory()->create();

    $this->get(route('admin.activity-log.index', ['causer_id' => $other->id]))
        ->assertInertia(fn (Assert $page) => $page->has('activities.data', 0));
});

test('detail aktivitas memuat nilai lama dan baru', function () {
    $this->actingAs($this->user);

    $certificate = Certificate::factory()->create(['issuer' => 'Lama']);
    $certificate->update(['issuer' => 'Baru']);

    $this->get(route('admin.activity-log.index', ['event' => 'updated']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('activities.data.0.changes.old.issuer', 'Lama')
            ->where('activities.data.0.changes.attributes.issuer', 'Baru')
            ->where('activities.data.0.causer_name', $this->user->name),
        );
});

test('log aktivitas tidak bisa dihapus lewat route', function () {
    $this->actingAs($this->user);

    Certificate::factory()->create();

    $this->delete('/admin/activity-log/1')->assertNotFound();

    // Catatan pembuatan pengguna dan sertifikat tetap utuh.
    expect(Activity::query()->count())->toBe(2);
});

test('command pembersihan menghapus catatan lebih tua dari batas', function () {
    $this->actingAs($this->user);

    Certificate::factory()->create();

    Activity::query()->update(['created_at' => now()->subMonths(13)]);
    Certificate::factory()->create();

    $this->artisan('activity-log:prune')
        ->expectsOutputToContain('2 catatan aktivitas lebih tua dari 12 bulan dihapus.')
        ->assertSuccessful();

    expect(Activity::query()->count())->toBe(1);
});
