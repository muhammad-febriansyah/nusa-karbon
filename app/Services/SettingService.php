<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SettingType;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Pembungkus tabel `site_settings`: cache permanen, invalidasi saat berubah,
 * dan enkripsi kunci rahasia.
 *
 * Nilai disimpan sebagai JSON `{"data": …}` supaya kolomnya bisa menampung
 * skalar maupun larik terjemahan tanpa berubah tipe.
 *
 * @see docs/04-erd.md bagian 3.7
 */
final class SettingService
{
    private const CACHE_KEY = 'site_settings.all';

    public function __construct(private readonly MediaUploadService $media) {}

    /**
     * Seluruh pengaturan sebagai peta kunci ke nilai siap pakai.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return SiteSetting::query()
                ->get()
                ->mapWithKeys(fn (SiteSetting $setting): array => [
                    $setting->key => $setting->plainValue(),
                ])
                ->all();
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Pengaturan yang boleh dikirim ke halaman publik. Kunci rahasia tidak
     * pernah ikut, bahkan bila ditandai publik karena kekeliruan seeder.
     *
     * Nilainya sudah diselesaikan: kolom translatable jadi string untuk locale
     * aktif, dan path berkas jadi URL siap pakai. Frontend publik tidak perlu
     * tahu bahwa terjemahan disimpan sebagai peta locale, dan tidak perlu ikut
     * memikirkan berkasnya ada di disk mana.
     *
     * @return array<string, mixed>
     */
    public function publicSettings(): array
    {
        $locale = app()->getLocale();

        return SiteSetting::query()
            ->public()
            ->get()
            ->reject(fn (SiteSetting $setting): bool => $setting->isSecret())
            ->mapWithKeys(fn (SiteSetting $setting): array => [
                $setting->key => $this->resolveForPublic($setting, $locale),
            ])
            ->all();
    }

    /**
     * Menyelesaikan satu nilai pengaturan untuk halaman publik.
     */
    private function resolveForPublic(SiteSetting $setting, string $locale): mixed
    {
        $value = $setting->plainValue();

        return match ($setting->type) {
            SettingType::Translatable => $this->translatedValue($value, $locale),
            SettingType::Image, SettingType::File => is_string($value) && $value !== ''
                ? Storage::disk('public')->url($value)
                : null,
            default => $value,
        };
    }

    /**
     * Terjemahan untuk locale aktif. Bila kosong, bahasa mana pun yang sudah
     * terisi dipakai sebagai cadangan — halaman publik lebih baik menampilkan
     * teks bahasa lain daripada bagian yang kosong.
     */
    private function translatedValue(mixed $value, string $locale): ?string
    {
        if (! is_array($value)) {
            return is_string($value) && $value !== '' ? $value : null;
        }

        $candidates = array_filter(
            [$value[$locale] ?? null, ...array_values($value)],
            fn (mixed $text): bool => is_string($text) && trim($text) !== '',
        );

        $first = reset($candidates);

        return is_string($first) ? $first : null;
    }

    /**
     * Pengaturan per grup untuk halaman admin, lengkap dengan metadata field.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function grouped(): array
    {
        return SiteSetting::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->map(fn (Collection $settings): array => $settings
                ->map(fn (SiteSetting $setting): array => [
                    'key' => $setting->key,
                    'label' => $setting->label,
                    'description' => $setting->description,
                    'type' => $setting->type->value,
                    'is_secret' => $setting->isSecret(),
                    // Nilai rahasia tidak pernah dikirim ke frontend; admin
                    // hanya bisa menimpanya, bukan membacanya.
                    'value' => $setting->isSecret() ? null : $setting->plainValue(),
                    'has_value' => $setting->plainValue() !== null,
                    // Berkas disimpan sebagai path; frontend butuh URL-nya
                    // untuk pratinjau.
                    'file_url' => $this->fileUrl($setting),
                ])
                ->values()
                ->all())
            ->all();
    }

    /**
     * Menyimpan sekumpulan nilai sekaligus lalu mengosongkan cache.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        DB::transaction(function () use ($values): void {
            $settings = SiteSetting::query()
                ->whereIn('key', array_keys($values))
                ->get()
                ->keyBy('key');

            foreach ($values as $key => $value) {
                $setting = $settings->get($key);

                if (! $setting instanceof SiteSetting) {
                    continue;
                }

                // Nilai rahasia yang dikirim kosong berarti "biarkan apa
                // adanya" — bukan perintah menghapus kredensial.
                if ($setting->isSecret() && ($value === null || $value === '')) {
                    continue;
                }

                $setting->update(['value' => ['data' => $this->prepare($setting, $value)]]);
            }
        });

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function fileUrl(SiteSetting $setting): ?string
    {
        $isFile = $setting->type === SettingType::Image || $setting->type === SettingType::File;
        $value = $setting->plainValue();

        if (! $isFile || ! is_string($value) || $value === '') {
            return null;
        }

        return Storage::disk('public')->url($value);
    }

    private function prepare(SiteSetting $setting, mixed $value): mixed
    {
        if ($setting->isSecret() && is_string($value)) {
            return Crypt::encryptString($value);
        }

        // Berkas dikirim sebagai token unggahan sementara; yang disimpan
        // adalah path akhirnya di disk publik.
        if (($setting->type === SettingType::Image || $setting->type === SettingType::File)
            && is_string($value)
            && Str::isUlid($value)) {
            $file = $this->media->moveFromToken($value, SiteSetting::FILE_DIRECTORY);

            return $file['path'] ?? $setting->plainValue();
        }

        return match ($setting->type) {
            SettingType::Boolean => (bool) $value,
            SettingType::Number, SettingType::Coordinate => $value === null || $value === ''
                ? null
                : (float) $value,
            default => $value,
        };
    }
}
