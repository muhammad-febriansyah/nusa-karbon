<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * Sejak activitylog v5, nilai lama dan baru disimpan di kolom
 * `attribute_changes`, bukan lagi di dalam `properties`.
 */
test('perubahan nama pengguna tercatat beserta nilai lama dan baru', function () {
    $user = User::factory()->create(['name' => 'Adi Wicaksono']);

    $user->update(['name' => 'Adi W.']);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('user')
        ->and($activity->attribute_changes['old']['name'])->toBe('Adi Wicaksono')
        ->and($activity->attribute_changes['attributes']['name'])->toBe('Adi W.');
});

test('kredensial tidak pernah masuk ke activity log', function () {
    $user = User::factory()->create();

    $user->update([
        'name' => 'Nama Baru',
        'password' => 'kata-sandi-baru',
    ]);

    $activity = Activity::query()->where('event', 'updated')->latest('id')->first();
    $changes = $activity->attribute_changes->toArray();

    expect($changes)
        ->not->toHaveKey('attributes.password')
        ->not->toHaveKey('old.password')
        ->not->toHaveKey('attributes.remember_token')
        ->not->toHaveKey('attributes.two_factor_secret');
});

test('penyimpanan tanpa perubahan tidak menghasilkan catatan', function () {
    $user = User::factory()->create();

    $countAfterCreate = Activity::query()->count();

    $user->save();

    expect(Activity::query()->count())->toBe($countAfterCreate);
});
