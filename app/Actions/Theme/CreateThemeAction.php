<?php

declare(strict_types=1);

namespace App\Actions\Theme;

use App\Models\Theme;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use Illuminate\Support\Str;

final readonly class CreateThemeAction
{
    public function __construct(private ThemeRepositoryInterface $themes) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Theme
    {
        $data['slug'] = $this->uniqueSlug((string) ($data['slug'] ?? $data['name']));
        $data['is_preset'] = false;

        return $this->themes->create($data);
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $suffix = 2;

        while ($this->themes->slugExists($slug)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
