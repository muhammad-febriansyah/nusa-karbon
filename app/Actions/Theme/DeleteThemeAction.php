<?php

declare(strict_types=1);

namespace App\Actions\Theme;

use App\Exceptions\ActiveThemeException;
use App\Exceptions\PresetThemeException;
use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;

final readonly class DeleteThemeAction
{
    public function __construct(private ThemeRepositoryInterface $themes) {}

    /**
     * @throws PresetThemeException bila tema adalah preset bawaan
     * @throws ActiveThemeException bila tema sedang dipakai
     */
    public function handle(Theme $theme): bool
    {
        if ($theme->is_preset) {
            throw new PresetThemeException;
        }

        if ($theme->is_active_admin || $theme->is_active_public) {
            throw new ActiveThemeException;
        }

        return $this->themes->delete($theme);
    }
}
