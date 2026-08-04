<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Theme;

use App\Models\Theme;

final class UpdateThemeRequest extends StoreThemeRequest
{
    protected function currentThemeId(): ?int
    {
        $theme = $this->route('theme');

        return $theme instanceof Theme ? $theme->id : null;
    }
}
