<?php

declare(strict_types=1);

use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\ThemeSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(SiteSettingSeeder::class);
});

test('halaman publik menerima pengaturan situs, halaman admin tidak', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('settings.company_name'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('settings', []));
});

test('kunci rahasia tidak pernah ikut ke halaman publik', function () {
    app(SettingService::class)->setMany(['turnstile_secret_key' => 'rahasia-sekali']);

    $response = $this->get(route('public.home'))->assertOk();

    expect($response->viewData('page')['props']['settings'])
        ->not->toHaveKey('turnstile_secret_key');
});

test('pengaturan translatable dikirim sebagai teks, bukan peta locale', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->whereType('settings.tagline', 'string'),
        );
});

test('path berkas pengaturan dikirim sebagai URL siap pakai', function () {
    SiteSetting::query()
        ->where('key', 'default_og_image')
        ->update(['value' => ['data' => 'seo/og.png']]);

    app(SettingService::class)->flush();

    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.default_og_image', fn (string $url): bool => str_ends_with($url, '/storage/seo/og.png')),
        );
});

test('halaman publik memakai tema publik, halaman admin memakai tema admin', function () {
    $this->seed(ThemeSeeder::class);

    $publicCss = $this->get(route('public.home'))
        ->assertOk()
        ->viewData('page')['props']['theme']['css'];

    $adminCss = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->viewData('page')['props']['theme']['css'];

    expect($publicCss)->not->toBe($adminCss)
        ->and($publicCss)->toContain('--t-background');
});

test('daftar locale dan basis SEO ikut dikirim ke halaman publik', function () {
    $this->get(route('public.home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale.current', config('locales.default'))
            ->where('locale.direction', 'ltr')
            ->has('locale.supported', count(config('locales.supported')))
            // Bahasa Fase 6 ikut dikirim untuk ditampilkan, tetapi tetap di
            // luar `supported` — apa pun di sana dianggap punya rute.
            ->has('locale.planned', count(config('locales.planned')))
            ->where('locale.planned.0.code', 'en')
            ->missing('locale.planned.0.prefix')
            ->where('seo.path', '/')
            ->has('seo.canonical'),
        );
});

test('kerangka publik dan prop bersama ikut pada halaman selain beranda', function () {
    /*
     * Dulu tes ini memeriksa `ComingSoonController`. Seluruh rute publik kini
     * punya controllernya sendiri, jadi yang dijaga sekarang hal yang lebih
     * berguna: prop bersama tetap terkirim di halaman mana pun, bukan hanya
     * beranda.
     */
    $this->get(route('public.contact'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/contact')
            ->has('locale.supported')
            ->has('brand.name')
            ->has('seo.canonical'),
        );
});
