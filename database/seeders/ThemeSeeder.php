<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Theme;
use App\Support\Color;
use Illuminate\Database\Seeder;

/**
 * Lima preset bawaan. Preset tidak bisa diubah maupun dihapus admin — untuk
 * memodifikasinya, admin menduplikasi lebih dulu.
 *
 * @see docs/04-erd.md bagian themes
 */
final class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->presets() as $preset) {
            Theme::query()->firstOrCreate(
                ['slug' => $preset['slug']],
                [
                    'name' => $preset['name'],
                    'scope' => $preset['scope'],
                    'tokens' => $preset['tokens'],
                    'is_preset' => true,
                    'is_active_admin' => $preset['slug'] === Theme::DEFAULT_ADMIN_SLUG,
                    'is_active_public' => $preset['slug'] === Theme::DEFAULT_PUBLIC_SLUG,
                ],
            );
        }
    }

    /**
     * @return array<int, array{slug: string, name: string, scope: string, tokens: array<string, string>}>
     */
    private function presets(): array
    {
        return [
            [
                'slug' => 'ember-light',
                'name' => 'Ember Light',
                'scope' => Theme::SCOPE_BOTH,
                'tokens' => $this->tokens('#C94A17', '#8A6A17', [
                    'background' => '#F7F7F4',
                    'foreground' => '#131418',
                    'card' => '#FFFFFF',
                    'muted' => '#F1F1EE',
                    'muted_foreground' => '#5F6167',
                    'border' => '#E1E1DA',
                    'sidebar' => '#FAFAF8',
                    'sidebar_accent' => '#F0EFEB',
                ]),
            ],
            [
                'slug' => 'charcoal-dark',
                'name' => 'Charcoal Industrial',
                'scope' => Theme::SCOPE_PUBLIC,
                'tokens' => $this->tokens('#E2581F', '#C8A02C', [
                    'background' => '#16181C',
                    'foreground' => '#F4F4F2',
                    'card' => '#1D2026',
                    'muted' => '#23262D',
                    'muted_foreground' => '#A6A9B0',
                    'border' => '#2C3038',
                    'sidebar' => '#12141A',
                    'sidebar_accent' => '#20242C',
                ]),
            ],
            [
                /*
                 * Palet terang untuk website publik, diturunkan dari rupa
                 * produknya sendiri: briket batok yang terbakar bersih
                 * berselimut abu pucat di atas karbon hitam. Permukaannya
                 * mengambil warna abu itu — netral dingin dengan sedikit
                 * hijau, bukan krem — supaya tidak berubah jadi latar hangat
                 * yang sudah dipakai ribuan situs.
                 *
                 * Oranye merek diperdalam ke #B8410F karena #E2581F hanya
                 * mencapai 3,7:1 di atas permukaan terang; versi ini lolos
                 * 4,9:1 sebagai teks sekaligus 5,6:1 sebagai latar tombol.
                 * Aksen hijau sabut jadi penyeimbang dingin agar halaman tidak
                 * hanya hitam-putih-oranye.
                 */
                'slug' => 'ash-carbon',
                'name' => 'Kapur & Karbon',
                'scope' => Theme::SCOPE_BOTH,
                'tokens' => $this->tokens('#B8410F', '#2F6B5F', [
                    'background' => '#EFF1EE',
                    'foreground' => '#15181A',
                    'card' => '#FFFFFF',
                    'muted' => '#E4E7E2',
                    'muted_foreground' => '#5A5F5C',
                    'border' => '#D2D6CF',
                    'sidebar' => '#F6F7F5',
                    'sidebar_accent' => '#E7EAE4',
                ]),
            ],
            [
                'slug' => 'slate-neutral',
                'name' => 'Slate Neutral',
                'scope' => Theme::SCOPE_BOTH,
                'tokens' => $this->tokens('#334155', '#0F766E', [
                    'background' => '#F8FAFC',
                    'foreground' => '#0F172A',
                    'card' => '#FFFFFF',
                    'muted' => '#F1F5F9',
                    'muted_foreground' => '#55627A',
                    'border' => '#E2E8F0',
                    'sidebar' => '#FFFFFF',
                    'sidebar_accent' => '#EEF2F7',
                ]),
            ],
            [
                'slug' => 'forest-green',
                'name' => 'Forest Green',
                'scope' => Theme::SCOPE_BOTH,
                'tokens' => $this->tokens('#166534', '#B45309', [
                    'background' => '#F6F8F5',
                    'foreground' => '#14211A',
                    'card' => '#FFFFFF',
                    'muted' => '#EDF2EC',
                    'muted_foreground' => '#546056',
                    'border' => '#DDE5DB',
                    'sidebar' => '#FBFCFA',
                    'sidebar_accent' => '#E9F0E7',
                ]),
            ],
            [
                'slug' => 'navy-corporate',
                'name' => 'Navy Corporate',
                'scope' => Theme::SCOPE_BOTH,
                'tokens' => $this->tokens('#1E3A8A', '#B45309', [
                    'background' => '#F7F8FC',
                    'foreground' => '#111827',
                    'card' => '#FFFFFF',
                    'muted' => '#EEF1F8',
                    'muted_foreground' => '#525C74',
                    'border' => '#DCE1EE',
                    'sidebar' => '#FFFFFF',
                    'sidebar_accent' => '#E8EDF9',
                ]),
            ],
        ];
    }

    /**
     * Menyusun satu set token lengkap dari warna brand dan permukaan.
     * Token turunan mengikuti pola yang sama di seluruh preset supaya
     * kontrasnya konsisten.
     *
     * @param  array<string, string>  $surface
     * @return array<string, string>
     */
    private function tokens(string $primary, string $accent, array $surface): array
    {
        $foreground = $surface['foreground'];

        // Teks di atas warna solid dihitung, bukan diasumsikan putih: gold
        // terang butuh teks gelap agar lolos ambang 4,5:1.
        $on = static fn (string $color): string => Color::bestForeground($color);

        return [
            'primary' => $primary,
            'primary_foreground' => $on($primary),
            'secondary' => $surface['muted'],
            'secondary_foreground' => $foreground,
            'accent' => $accent,
            'accent_foreground' => $on($accent),

            'background' => $surface['background'],
            'foreground' => $foreground,
            'card' => $surface['card'],
            'card_foreground' => $foreground,
            'popover' => $surface['card'],
            'popover_foreground' => $foreground,
            'muted' => $surface['muted'],
            'muted_foreground' => $surface['muted_foreground'],
            'border' => $surface['border'],
            'input' => $surface['border'],
            'ring' => $primary,

            'success' => '#15803D',
            'success_foreground' => $on('#15803D'),
            'destructive' => '#B91C1C',
            'destructive_foreground' => $on('#B91C1C'),
            'warning' => '#B45309',
            'warning_foreground' => $on('#B45309'),
            'info' => '#1D4ED8',
            'info_foreground' => $on('#1D4ED8'),
            'neutral' => '#4B5563',
            'neutral_foreground' => $on('#4B5563'),
            'violet' => '#6D28D9',
            'violet_foreground' => $on('#6D28D9'),
            'teal' => '#0F766E',
            'teal_foreground' => $on('#0F766E'),

            'sidebar' => $surface['sidebar'],
            'sidebar_foreground' => $foreground,
            'sidebar_accent' => $surface['sidebar_accent'],
            'sidebar_accent_foreground' => $foreground,
            'sidebar_primary' => $primary,
            'sidebar_primary_foreground' => $on($primary),
            'sidebar_border' => $surface['border'],
            'sidebar_ring' => $primary,

            'radius' => '0.5rem',
        ];
    }
}
