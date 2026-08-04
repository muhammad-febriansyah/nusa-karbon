<?php

declare(strict_types=1);

namespace App\Actions\Theme;

use App\Models\Theme;

/**
 * Menyalin tema menjadi versi yang bisa diedit. Inilah jalan resmi untuk
 * memodifikasi preset bawaan.
 */
final readonly class DuplicateThemeAction
{
    public function __construct(private CreateThemeAction $create) {}

    public function handle(Theme $theme): Theme
    {
        return $this->create->handle([
            'name' => "Salinan {$theme->name}",
            'scope' => $theme->scope,
            'tokens' => $theme->tokens,
        ]);
    }
}
