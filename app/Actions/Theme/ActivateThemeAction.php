<?php

declare(strict_types=1);

namespace App\Actions\Theme;

use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use App\Services\SettingService;
use App\Services\ThemeService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Mengaktifkan satu tema untuk satu scope. Hanya boleh ada satu tema aktif per
 * scope — ditegakkan lewat transaksi, bukan constraint database.
 *
 * @see docs/10-theming.md bagian 5
 */
final readonly class ActivateThemeAction
{
    public function __construct(
        private ThemeRepositoryInterface $themes,
        private ThemeService $themeService,
        private SettingService $settings,
    ) {}

    /**
     * @throws InvalidArgumentException bila tema tidak mendukung scope tersebut
     */
    public function handle(Theme $theme, string $scope): Theme
    {
        if (! $theme->supportsScope($scope)) {
            throw new InvalidArgumentException(
                'Tema ini tidak tersedia untuk lingkup yang dipilih.',
            );
        }

        $column = $scope === Theme::SCOPE_ADMIN ? 'is_active_admin' : 'is_active_public';

        DB::transaction(function () use ($theme, $scope, $column): void {
            $this->themes->deactivateScope($scope);
            $this->themes->update($theme, [$column => true]);

            $this->settings->setMany(["active_theme_{$scope}" => $theme->id]);
        });

        // Cache dikosongkan setelah transaksi commit. Bila dikosongkan di
        // dalamnya lalu transaksi gagal, pengguna sempat melihat tema keliru.
        $this->themeService->flush();

        return $theme->refresh();
    }
}
