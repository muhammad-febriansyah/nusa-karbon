<?php

declare(strict_types=1);

namespace App\Actions\Theme;

use App\Exceptions\PresetThemeException;
use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use App\Services\ThemeService;

final readonly class UpdateThemeAction
{
    public function __construct(
        private ThemeRepositoryInterface $themes,
        private ThemeService $themeService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws PresetThemeException bila tema adalah preset bawaan
     */
    public function handle(Theme $theme, array $data): Theme
    {
        if ($theme->is_preset) {
            throw new PresetThemeException;
        }

        // Slug tidak ikut berubah: alamat halaman dan referensi cache tetap
        // stabil meski namanya diubah.
        unset($data['slug'], $data['is_preset']);

        $theme = $this->themes->update($theme, $data);

        // Tema yang sedang aktif langsung terpakai, jadi cache-nya dibuang.
        if ($theme->is_active_admin || $theme->is_active_public) {
            $this->themeService->flush();
        }

        return $theme;
    }
}
