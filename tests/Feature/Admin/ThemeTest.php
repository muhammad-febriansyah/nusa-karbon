<?php

declare(strict_types=1);

use App\Models\Theme;
use App\Models\User;
use App\Services\SettingService;
use App\Services\ThemeService;
use App\Support\Color;
use Database\Factories\ThemeFactory;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Activitylog\Models\Activity;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->seed(SiteSettingSeeder::class);
    Cache::flush();
});

/**
 * @param  array<string, string>  $overrides
 * @return array<string, mixed>
 */
function themePayload(array $overrides = []): array
{
    return [
        'name' => 'Navy Corporate',
        'scope' => Theme::SCOPE_BOTH,
        'tokens' => [...ThemeFactory::defaultTokens(), ...$overrides],
    ];
}

test('tamu tidak bisa mengakses tema', function () {
    $this->get(route('admin.themes.index'))
        ->assertRedirect(route('login'));
});

test('seeder mengisi enam preset dan menandai tema aktif', function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(ThemeSeeder::class);

    expect(Theme::query()->count())->toBe(6)
        ->and(Theme::query()->where('is_preset', true)->count())->toBe(6)
        ->and(Theme::query()->where('is_active_admin', true)->value('slug'))->toBe(Theme::DEFAULT_ADMIN_SLUG)
        ->and(Theme::query()->where('is_active_public', true)->value('slug'))->toBe(Theme::DEFAULT_PUBLIC_SLUG);
});

test('halaman daftar menampilkan tema beserta swatch', function () {
    $this->seed(ThemeSeeder::class);

    $this->actingAs($this->user)
        ->get(route('admin.themes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/themes/index')
            ->has('themes', 6)
            ->has('themes.0.swatches')
            ->has('scopes', 3),
        );
});

test('tema baru tersimpan dengan slug unik', function () {
    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload())
        ->assertRedirect(route('admin.themes.index'))
        ->assertSessionHasNoErrors();

    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload())
        ->assertSessionHasErrors('name');

    expect(Theme::query()->count())->toBe(1)
        ->and(Theme::query()->first()->slug)->toBe('navy-corporate')
        ->and(Theme::query()->first()->is_preset)->toBeFalse();
});

test('warna selain hex enam digit ditolak', function (string $value) {
    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload(['primary' => $value]))
        ->assertSessionHasErrors('tokens.primary');

    expect(Theme::query()->count())->toBe(0);
})->with([
    'hex tiga digit' => '#FFF',
    'rgb' => 'rgb(255, 0, 0)',
    'nama warna' => 'red',
    'tanpa pagar' => 'C94A17',
]);

test('token tidak dikenal maupun yang hilang ditolak', function () {
    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload(['token_liar' => '#FFFFFF']))
        ->assertSessionHasErrors('tokens');

    $incomplete = themePayload();
    unset($incomplete['tokens']['primary']);

    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), $incomplete)
        ->assertSessionHasErrors('tokens.primary');
});

test('kontras di bawah 4,5:1 ditolak beserta angkanya', function () {
    // Kuning muda dengan teks putih: sekitar 1,1:1.
    $response = $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload([
            'primary' => '#FFE680',
            'primary_foreground' => '#FFFFFF',
        ]));

    $response->assertSessionHasErrors('tokens.primary_foreground');

    $message = session('errors')->first('tokens.primary_foreground');

    expect($message)->toContain('4,5:1')
        ->and(Theme::query()->count())->toBe(0);
});

test('pasangan peringatan tidak menghalangi simpan', function () {
    // muted_foreground sengaja redup: ambangnya hanya peringatan.
    $this->actingAs($this->user)
        ->post(route('admin.themes.store'), themePayload([
            'muted' => '#F1F1EE',
            'muted_foreground' => '#B4B4B0',
        ]))
        ->assertSessionHasNoErrors();

    expect(Theme::query()->count())->toBe(1);
});

test('preset tidak bisa diubah maupun dihapus', function () {
    $this->seed(ThemeSeeder::class);

    $preset = Theme::query()->where('slug', 'slate-neutral')->firstOrFail();

    // Nama dibiarkan sama supaya validasi slug lolos dan yang menolak
    // benar-benar proteksi preset, bukan aturan nama unik.
    $this->actingAs($this->user)
        ->put(route('admin.themes.update', $preset), [
            ...themePayload(),
            'name' => $preset->name,
        ])
        ->assertSessionHasErrors('theme');

    expect($preset->refresh()->name)->toBe('Slate Neutral');

    $this->actingAs($this->user)
        ->delete(route('admin.themes.destroy', $preset))
        ->assertSessionHasErrors('theme');

    expect(Theme::query()->whereKey($preset->id)->exists())->toBeTrue();
});

test('duplikat membuat salinan yang bisa diedit', function () {
    $this->seed(ThemeSeeder::class);

    $preset = Theme::query()->where('slug', 'ember-light')->firstOrFail();

    $this->actingAs($this->user)
        ->post(route('admin.themes.duplicate', $preset))
        ->assertRedirect();

    $copy = Theme::query()->where('is_preset', false)->firstOrFail();

    expect($copy->name)->toBe('Salinan Ember Light')
        ->and($copy->tokens)->toBe($preset->tokens)
        ->and($copy->is_active_admin)->toBeFalse();
});

test('hanya satu tema aktif per scope dan cache dikosongkan', function () {
    $this->seed(ThemeSeeder::class);

    $themes = app(ThemeService::class);
    $settings = app(SettingService::class);

    $first = Theme::query()->where('slug', 'ember-light')->firstOrFail();
    $second = Theme::query()->where('slug', 'navy-corporate')->firstOrFail();

    expect($themes->tokensFor(Theme::SCOPE_ADMIN)['primary'])->toBe($first->tokens['primary']);

    $this->actingAs($this->user)
        ->put(route('admin.themes.activate', $second), ['scope' => Theme::SCOPE_ADMIN])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(Theme::query()->where('is_active_admin', true)->count())->toBe(1)
        ->and($second->refresh()->is_active_admin)->toBeTrue()
        ->and($first->refresh()->is_active_admin)->toBeFalse()
        ->and($themes->tokensFor(Theme::SCOPE_ADMIN)['primary'])->toBe($second->tokens['primary'])
        ->and($settings->get('active_theme_admin'))->toEqual($second->id);
});

test('tema khusus publik tidak bisa diaktifkan untuk admin', function () {
    $this->seed(ThemeSeeder::class);

    $publicOnly = Theme::query()->where('slug', 'charcoal-dark')->firstOrFail();

    $this->actingAs($this->user)
        ->put(route('admin.themes.activate', $publicOnly), ['scope' => Theme::SCOPE_ADMIN])
        ->assertSessionHasErrors('scope');

    expect($publicOnly->refresh()->is_active_admin)->toBeFalse();
});

test('tema aktif tidak bisa dihapus', function () {
    $theme = Theme::factory()->activeAdmin()->create();

    $this->actingAs($this->user)
        ->delete(route('admin.themes.destroy', $theme))
        ->assertSessionHasErrors('theme');

    expect(Theme::query()->whereKey($theme->id)->exists())->toBeTrue();
});

test('CSS variable dirender server dan menolak nilai tidak valid', function () {
    $theme = Theme::factory()->activeAdmin()->create([
        'tokens' => [
            ...ThemeFactory::defaultTokens(),
            'primary' => '#123456',
            // Nilai berbahaya di database tidak boleh sampai ke <style>.
            'accent' => 'red;} body{display:none',
        ],
    ]);

    $css = app(ThemeService::class)->toCssVariables($theme->tokens);

    expect($css)->toStartWith(':root{')
        ->and($css)->toContain('--t-primary:#123456')
        ->and($css)->not->toContain('display:none')
        ->and($css)->toContain('--t-radius:0.5rem');
});

test('halaman admin mengirim CSS tema lewat shared props', function () {
    Theme::factory()->activeAdmin()->create();

    $this->actingAs($this->user)
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where(
            'theme.css',
            fn (string $css): bool => str_contains($css, '--t-primary'),
        ));
});

test('perubahan tema tercatat di activity log', function () {
    $theme = Theme::factory()->create();

    $this->actingAs($this->user)
        ->put(route('admin.themes.update', $theme), themePayload([
            'primary' => '#123456',
        ]))
        ->assertSessionHasNoErrors();

    $activity = Activity::query()
        ->where('log_name', 'theme')
        ->where('event', 'updated')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($this->user->id);
});

test('bestForeground memilih hitam atau putih yang paling terbaca', function () {
    expect(Color::bestForeground('#FFE680'))->toBe('#111111')
        ->and(Color::bestForeground('#1E3A8A'))->toBe('#FFFFFF')
        ->and(Color::contrastRatio('#FFFFFF', '#000000'))->toEqualWithDelta(21.0, 0.01);
});

test('hasil kosong tidak ikut di-cache sehingga seeder menyusul tetap terbaca', function () {
    $themes = app(ThemeService::class);

    // Aplikasi melayani request sebelum tabel `themes` terisi.
    expect($themes->tokensFor(Theme::SCOPE_PUBLIC))->toBe([])
        ->and(Cache::has('theme.tokens.public'))->toBeFalse();

    $this->seed(ThemeSeeder::class);

    // Tanpa flush manual: request berikutnya sudah melihat tema yang benar.
    expect($themes->tokensFor(Theme::SCOPE_PUBLIC))
        ->toHaveKey('background')
        ->and(Cache::has('theme.tokens.public'))->toBeTrue();
});
