<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\Theme;
use App\Support\Color;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Theme
 */
final class ThemeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'scope' => $this->scope,
            'tokens' => $this->tokens,
            'is_preset' => $this->is_preset,
            'is_active_admin' => $this->is_active_admin,
            'is_active_public' => $this->is_active_public,
            // Enam warna untuk pratinjau palet di daftar tema.
            'swatches' => array_values(array_filter([
                $this->tokens['primary'] ?? null,
                $this->tokens['accent'] ?? null,
                $this->tokens['background'] ?? null,
                $this->tokens['card'] ?? null,
                $this->tokens['success'] ?? null,
                $this->tokens['destructive'] ?? null,
            ])),
            'contrast' => $this->when(
                $request->routeIs('admin.themes.edit', 'admin.themes.create'),
                fn (): array => $this->contrastReport(),
            ),
            'created_at' => $this->created_at?->format('d M Y, H:i'),
        ];
    }

    /**
     * Rasio kontras tiap pasangan token; dipakai panel pemeriksaan di editor.
     *
     * @return array<int, array{background: string, foreground: string, ratio: float, minimum: float, blocking: bool, passes: bool}>
     */
    private function contrastReport(): array
    {
        $report = [];

        foreach (Theme::CONTRAST_PAIRS as [$background, $foreground, $minimum, $blocking]) {
            $backgroundValue = $this->tokens[$background] ?? null;
            $foregroundValue = $this->tokens[$foreground] ?? null;

            if (! is_string($backgroundValue) || ! is_string($foregroundValue)) {
                continue;
            }

            $ratio = Color::contrastRatio($backgroundValue, $foregroundValue);

            $report[] = [
                'background' => $background,
                'foreground' => $foreground,
                'ratio' => round($ratio, 2),
                'minimum' => $minimum,
                'blocking' => $blocking,
                'passes' => $ratio + 0.005 >= $minimum,
            ];
        }

        return $report;
    }
}
