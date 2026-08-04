<?php

declare(strict_types=1);

use App\Enums\SettingType;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->seed(SiteSettingSeeder::class);
});

test('tamu tidak bisa mengakses pengaturan situs', function () {
    $this->get(route('admin.site-settings.index'))
        ->assertRedirect(route('login'));
});

test('halaman pengaturan mengelompokkan seluruh kunci', function () {
    $this->actingAs($this->user)
        ->get(route('admin.site-settings.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/site-settings/index')
            ->has('groups', 6)
            ->where('groups.0.key', 'identity')
            ->has('groups.0.settings'),
        );
});

test('seeder mengisi seluruh kunci tanpa duplikat', function () {
    $this->seed(SiteSettingSeeder::class);

    expect(SiteSetting::query()->count())->toBe(56)
        ->and(SiteSetting::query()->where('group', 'identity')->count())->toBe(11)
        ->and(SiteSetting::query()->where('group', 'seo')->count())->toBe(12);
});

test('pengaturan tersimpan dan cache diperbarui', function () {
    $settings = app(SettingService::class);

    expect($settings->get('company_name'))->toBe('Nusacarbon');

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['company_name' => 'Nusacarbon Global'],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($settings->get('company_name'))->toBe('Nusacarbon Global');
});

test('kunci rahasia disimpan terenkripsi dan tidak dikirim ke frontend', function () {
    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['turnstile_secret_key' => 'rahasia-sekali'],
        ])
        ->assertSessionHasNoErrors();

    $setting = SiteSetting::query()->where('key', 'turnstile_secret_key')->firstOrFail();
    $stored = $setting->value['data'];

    expect($stored)->not->toBe('rahasia-sekali')
        ->and(Crypt::decryptString($stored))->toBe('rahasia-sekali')
        ->and($setting->plainValue())->toBe('rahasia-sekali');

    $this->actingAs($this->user)
        ->get(route('admin.site-settings.index'))
        ->assertInertia(function (Assert $page): void {
            $groups = $page->toArray()['props']['groups'];
            $system = collect($groups)->firstWhere('key', 'system');
            $secret = collect($system['settings'])->firstWhere('key', 'turnstile_secret_key');

            expect($secret['value'])->toBeNull()
                ->and($secret['is_secret'])->toBeTrue()
                ->and($secret['has_value'])->toBeTrue();
        });
});

test('kunci rahasia yang dikirim kosong tidak menghapus nilai lama', function () {
    $settings = app(SettingService::class);

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['turnstile_secret_key' => 'kunci-awal'],
        ]);

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['turnstile_secret_key' => ''],
        ])
        ->assertSessionHasNoErrors();

    expect($settings->get('turnstile_secret_key'))->toBe('kunci-awal');
});

test('pengaturan rahasia tidak pernah ikut ke frontend publik', function () {
    $settings = app(SettingService::class);

    $public = $settings->publicSettings();

    expect($public)->toHaveKey('company_name')
        ->and($public)->not->toHaveKey('turnstile_secret_key')
        ->and($public)->not->toHaveKey('maintenance_mode');
});

test('daftar email menolak alamat tidak valid', function () {
    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['rfq_notification_recipients' => 'sales@nusacarbon.com, bukan-email'],
        ])
        ->assertSessionHasErrors('values.rfq_notification_recipients');

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['rfq_notification_recipients' => 'sales@nusacarbon.com, ops@nusacarbon.com'],
        ])
        ->assertSessionHasNoErrors();
});

test('nilai numerik dan boolean disimpan dengan tipe yang benar', function () {
    $settings = app(SettingService::class);

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => [
                'rfq_rate_limit_per_hour' => '9',
                'robots_index' => false,
            ],
        ])
        ->assertSessionHasNoErrors();

    // JSON menulis 9.0 sebagai 9, jadi dibandingkan longgar tanpa tipe.
    expect($settings->get('rfq_rate_limit_per_hour'))->toEqual(9)
        ->and($settings->get('robots_index'))->toBeFalse();
});

test('perubahan pengaturan tercatat di activity log', function () {
    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['company_name' => 'Nusacarbon Global'],
        ]);

    $activity = Activity::query()
        ->where('log_name', 'site_setting')
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->user->id);
});

test('tipe pengaturan menentukan aturan validasi dasarnya', function () {
    SiteSetting::factory()->ofType(SettingType::Number, 5)->create(['key' => 'angka_uji']);

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['angka_uji' => 'bukan angka'],
        ])
        ->assertSessionHasErrors('values.angka_uji');
});

test('logo dan favicon tersimpan dari token unggahan', function () {
    Storage::fake('local');
    Storage::fake('public');

    $token = (string) Str::ulid();
    Storage::disk('local')->put("tmp-uploads/{$token}/logo.png", 'berkas-png');

    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => ['logo_primary' => $token],
        ])
        ->assertSessionHasNoErrors();

    expect(app(SettingService::class)->get('logo_primary'))->toBe('settings/logo.png')
        ->and(Storage::disk('public')->exists('settings/logo.png'))->toBeTrue();
});

test('menyimpan tanpa mengunggah ulang tidak menghapus logo yang tersimpan', function () {
    SiteSetting::query()->where('key', 'logo_primary')
        ->update(['value' => ['data' => 'settings/logo-lama.png']]);

    app(SettingService::class)->flush();

    // Form mengirim balik nilai lama saat tidak ada unggahan baru; ini yang
    // memastikan sisi server tidak menganggapnya perintah mengosongkan.
    $this->actingAs($this->user)
        ->put(route('admin.site-settings.update'), [
            'values' => [
                'logo_primary' => 'settings/logo-lama.png',
                'company_name' => 'Nusacarbon',
            ],
        ])
        ->assertSessionHasNoErrors();

    expect(app(SettingService::class)->get('logo_primary'))->toBe('settings/logo-lama.png');
});

test('identitas merek ikut dikirim sebagai shared props', function () {
    Storage::fake('public');

    SiteSetting::query()->where('key', 'logo_primary')
        ->update(['value' => ['data' => 'settings/logo.png']]);
    SiteSetting::query()->where('key', 'favicon')
        ->update(['value' => ['data' => 'settings/favicon.png']]);

    app(SettingService::class)->flush();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('brand.name', 'Nusacarbon')
            ->where('brand.logo', Storage::disk('public')->url('settings/logo.png'))
            ->where('brand.favicon', Storage::disk('public')->url('settings/favicon.png')),
        );
});

test('favicon dari pengaturan dipasang di head', function () {
    Storage::fake('public');

    SiteSetting::query()->where('key', 'favicon')
        ->update(['value' => ['data' => 'settings/favicon.png']]);

    app(SettingService::class)->flush();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertSee('settings/favicon.png', escape: false)
        ->assertDontSee('href="/favicon.ico"', escape: false);
});
