<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Theme\ActivateThemeAction;
use App\Actions\Theme\CreateThemeAction;
use App\Actions\Theme\DeleteThemeAction;
use App\Actions\Theme\DuplicateThemeAction;
use App\Actions\Theme\UpdateThemeAction;
use App\Exceptions\ActiveThemeException;
use App\Exceptions\PresetThemeException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Theme\StoreThemeRequest;
use App\Http\Requests\Admin\Theme\UpdateThemeRequest;
use App\Http\Resources\Admin\ThemeResource;
use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use App\Support\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final class ThemeController extends Controller
{
    public function __construct(
        private readonly ThemeRepositoryInterface $themes,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/themes/index', [
            'themes' => ThemeResource::collection($this->themes->ordered())->resolve(),
            'scopes' => Theme::scopeOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/themes/edit', [
            'theme' => null,
            'defaultTokens' => $this->defaultTokens(),
            'tokenGroups' => $this->tokenGroups(),
            'scopes' => Theme::scopeOptions(),
        ]);
    }

    public function store(StoreThemeRequest $request, CreateThemeAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tema berhasil dibuat.',
        ]);

        return to_route('admin.themes.index');
    }

    public function edit(Theme $theme): Response
    {
        return Inertia::render('admin/themes/edit', [
            'theme' => (new ThemeResource($theme))->resolve(),
            'defaultTokens' => $this->defaultTokens(),
            'tokenGroups' => $this->tokenGroups(),
            'scopes' => Theme::scopeOptions(),
        ]);
    }

    public function update(
        UpdateThemeRequest $request,
        Theme $theme,
        UpdateThemeAction $action,
    ): RedirectResponse {
        try {
            $action->handle($theme, $request->validated());
        } catch (PresetThemeException $exception) {
            return back()->withErrors(['theme' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan tema berhasil disimpan.',
        ]);

        return to_route('admin.themes.index');
    }

    public function destroy(Theme $theme, DeleteThemeAction $action): RedirectResponse
    {
        try {
            $action->handle($theme);
        } catch (PresetThemeException|ActiveThemeException $exception) {
            return back()->withErrors(['theme' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tema berhasil dihapus.',
        ]);

        return back();
    }

    public function duplicate(Theme $theme, DuplicateThemeAction $action): RedirectResponse
    {
        $copy = $action->handle($theme);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Salinan dibuat sebagai \"{$copy->name}\". Silakan ubah sesuai kebutuhan.",
        ]);

        return to_route('admin.themes.edit', $copy);
    }

    public function activate(
        Request $request,
        Theme $theme,
        ActivateThemeAction $action,
    ): RedirectResponse {
        $validated = $request->validate([
            'scope' => ['required', Rule::in([Theme::SCOPE_ADMIN, Theme::SCOPE_PUBLIC])],
        ]);

        try {
            $action->handle($theme, $validated['scope']);
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['scope' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $validated['scope'] === Theme::SCOPE_ADMIN
                ? "Tema \"{$theme->name}\" aktif untuk panel admin."
                : "Tema \"{$theme->name}\" aktif untuk website publik.",
        ]);

        return back();
    }

    /**
     * Palet awal untuk tema baru: preset default admin, atau warna aman bila
     * presetnya belum di-seed.
     *
     * @return array<string, string>
     */
    private function defaultTokens(): array
    {
        $preset = Theme::query()->where('slug', Theme::DEFAULT_ADMIN_SLUG)->first();

        if ($preset !== null) {
            return $preset->tokens;
        }

        $fallback = [];

        foreach (Theme::COLOR_KEYS as $key) {
            $fallback[$key] = str_ends_with($key, '_foreground')
                ? Color::bestForeground('#FFFFFF')
                : '#FFFFFF';
        }

        return [...$fallback, 'radius' => '0.5rem'];
    }

    /**
     * Pengelompokan field editor beserta keterangan tempat warna itu dipakai.
     * Tanpa keterangan ini admin tidak tahu bahwa mengubah `warning` akan
     * mengubah seluruh tombol Detail.
     *
     * @return array<int, array{key: string, label: string, tokens: array<int, array{key: string, label: string, hint: string|null}>}>
     */
    private function tokenGroups(): array
    {
        $hints = [
            'primary' => 'Tombol Tambah & Simpan, tautan aktif',
            'accent' => 'Sorotan sekunder dan ikon aksen',
            'background' => 'Latar halaman',
            'card' => 'Latar Card dan tabel',
            'muted' => 'Latar redup, baris kosong',
            'border' => 'Garis batas Card, tabel, dan input',
            'success' => 'Tombol Ubah, badge Aktif',
            'destructive' => 'Tombol Hapus, pesan error',
            'warning' => 'Tombol Detail, peringatan',
            'info' => 'Tombol Ekspor, Unggah, Filter',
            'neutral' => 'Tombol Batal, Kembali, Arsipkan',
            'violet' => 'Tombol Duplikat',
            'teal' => 'Tombol Aktifkan & Terbitkan',
            'sidebar' => 'Latar sidebar admin',
            'radius' => 'Kelengkungan sudut, contoh 0.5rem',
        ];

        $groups = [
            'brand' => ['Warna Brand', ['primary', 'primary_foreground', 'secondary', 'secondary_foreground', 'accent', 'accent_foreground']],
            'surface' => ['Permukaan', ['background', 'foreground', 'card', 'card_foreground', 'popover', 'popover_foreground', 'muted', 'muted_foreground', 'border', 'input', 'ring']],
            'semantic' => ['Warna Semantik', ['success', 'success_foreground', 'destructive', 'destructive_foreground', 'warning', 'warning_foreground', 'info', 'info_foreground', 'neutral', 'neutral_foreground', 'violet', 'violet_foreground', 'teal', 'teal_foreground']],
            'sidebar' => ['Sidebar & Bentuk', ['sidebar', 'sidebar_foreground', 'sidebar_accent', 'sidebar_accent_foreground', 'sidebar_primary', 'sidebar_primary_foreground', 'sidebar_border', 'sidebar_ring', 'radius']],
        ];

        return array_map(
            fn (string $key): array => [
                'key' => $key,
                'label' => $groups[$key][0],
                'tokens' => array_map(
                    fn (string $token): array => [
                        'key' => $token,
                        'label' => str_replace('_', ' ', $token),
                        'hint' => $hints[$token] ?? null,
                    ],
                    $groups[$key][1],
                ),
            ],
            array_keys($groups),
        );
    }
}
