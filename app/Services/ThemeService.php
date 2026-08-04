<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Theme;
use App\Support\Color;
use Illuminate\Support\Facades\Cache;

/**
 * Menyediakan token tema aktif sebagai CSS variable yang diinjeksi server ke
 * `<head>`. Tidak pernah lewat `useEffect`: menyuntik tema setelah hidrasi
 * menyebabkan kedipan warna default.
 *
 * @see docs/10-theming.md
 */
final class ThemeService
{
    private const CACHE_TTL_SECONDS = 60 * 60 * 24 * 30;

    /**
     * Token tema aktif untuk satu scope, dengan preset default sebagai
     * cadangan bila belum ada yang diaktifkan.
     *
     * @return array<string, string>
     */
    public function tokensFor(string $scope): array
    {
        /** @var array<string, string>|null $cached */
        $cached = Cache::get($this->cacheKey($scope));

        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        $tokens = $this->resolveTokens($scope);

        /*
         * Hasil kosong sengaja tidak ikut disimpan.
         *
         * Tabel `themes` kosong hanya pada satu keadaan: aplikasi sudah
         * melayani request tapi seeder belum dijalankan. Menyimpan larik kosong
         * pada saat itu mengunci situs ke warna cadangan selama 30 hari, dan
         * menjalankan seeder tidak memperbaikinya karena tidak ada yang
         * memanggil `flush()`. Membiarkannya tidak di-cache berarti biayanya
         * satu query per request selama jendela sempit itu saja.
         */
        if ($tokens !== []) {
            Cache::put($this->cacheKey($scope), $tokens, self::CACHE_TTL_SECONDS);
        }

        return $tokens;
    }

    /**
     * @return array<string, string>
     */
    private function resolveTokens(string $scope): array
    {
        $column = $scope === Theme::SCOPE_ADMIN ? 'is_active_admin' : 'is_active_public';
        $fallbackSlug = $scope === Theme::SCOPE_ADMIN
            ? Theme::DEFAULT_ADMIN_SLUG
            : Theme::DEFAULT_PUBLIC_SLUG;

        $theme = Theme::query()->where($column, true)->first()
            ?? Theme::query()->where('slug', $fallbackSlug)->first();

        return $theme === null ? [] : $theme->tokens;
    }

    /**
     * Menyusun blok CSS `:root`. Nilai yang bukan hex atau radius valid
     * dibuang — kolom database bukan tempat aman untuk dipercaya begitu saja.
     *
     * @param  array<string, string>  $tokens
     */
    public function toCssVariables(array $tokens): string
    {
        $lines = [];

        foreach ($tokens as $key => $value) {
            if (! $this->isSafeValue($key, $value)) {
                continue;
            }

            $name = '--t-'.str_replace('_', '-', $key);
            $lines[] = "{$name}:{$value}";
        }

        return $lines === [] ? '' : ':root{'.implode(';', $lines).'}';
    }

    public function flush(): void
    {
        Cache::forget($this->cacheKey(Theme::SCOPE_ADMIN));
        Cache::forget($this->cacheKey(Theme::SCOPE_PUBLIC));
    }

    private function cacheKey(string $scope): string
    {
        return "theme.tokens.{$scope}";
    }

    private function isSafeValue(string $key, string $value): bool
    {
        if ($key === 'radius') {
            return preg_match('/^\d+(\.\d+)?(rem|px)$/', $value) === 1;
        }

        return Color::isHex($value);
    }
}
