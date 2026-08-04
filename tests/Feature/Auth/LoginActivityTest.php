<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

test('login mencatat waktu dan IP terakhir', function () {
    $user = User::factory()->create([
        'last_login_at' => null,
        'last_login_ip' => null,
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $user->refresh();

    expect($user->last_login_at)->not->toBeNull()
        ->and($user->last_login_ip)->toBe('127.0.0.1');
});

test('pencatatan login tidak menyentuh updated_at', function () {
    $user = User::factory()->create();
    $updatedAt = $user->updated_at;

    $this->travelTo(now()->addHour());

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($user->refresh()->updated_at->timestamp)->toBe($updatedAt->timestamp);
});

test('pencatatan login tidak membanjiri activity log', function () {
    $user = User::factory()->create();
    $before = Activity::query()->count();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect(Activity::query()->count())->toBe($before);
});
