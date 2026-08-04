<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Models\SiteSetting;
use App\Support\Locale;
use Illuminate\Database\Seeder;

/**
 * Seluruh kunci pengaturan situs. Menambah pengaturan baru cukup menambah
 * entri di sini — halaman admin merender inputnya otomatis dari kolom `type`.
 *
 * @see docs/04-erd.md bagian 3.7
 */
final class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $locale = Locale::default();

        /** @var array<string, array<int, array{0: string, 1: SettingType, 2: string, 3?: mixed, 4?: bool}>> $groups */
        $groups = [
            'identity' => [
                ['company_name', SettingType::Str, 'Nama dagang', 'Nusacarbon'],
                ['legal_name', SettingType::Str, 'Nama badan hukum', 'PT Nusa Carbon Indonesia'],
                ['tagline', SettingType::Translatable, 'Tagline', [$locale => 'Briket arang batok kelapa kualitas ekspor']],
                ['about_short', SettingType::Translatable, 'Deskripsi singkat', [$locale => 'Trader dan mitra rantai pasok briket arang batok kelapa asal Indonesia.']],
                ['logo_primary', SettingType::Image, 'Logo'],
                ['favicon', SettingType::Image, 'Favicon'],
                ['founded_year', SettingType::Number, 'Tahun berdiri', 2019],
                ['nib', SettingType::Str, 'Nomor Induk Berusaha'],
                ['npwp', SettingType::Str, 'NPWP'],
                ['export_id', SettingType::Str, 'API-U / Exporter ID'],
                ['hs_code', SettingType::Str, 'HS Code', '4402.90.90'],
            ],
            'contact' => [
                ['address', SettingType::Translatable, 'Alamat lengkap', [$locale => '']],
                ['city', SettingType::Str, 'Kota'],
                ['province', SettingType::Str, 'Provinsi'],
                ['postal_code', SettingType::Str, 'Kode pos'],
                ['country', SettingType::Str, 'Negara', 'Indonesia'],
                ['map_lat', SettingType::Coordinate, 'Latitude'],
                ['map_lng', SettingType::Coordinate, 'Longitude'],
                ['map_embed_url', SettingType::Str, 'URL embed Google Maps'],
                ['phone', SettingType::Str, 'Telepon kantor'],
                ['whatsapp', SettingType::Str, 'WhatsApp (format internasional)'],
                ['whatsapp_default_message', SettingType::Translatable, 'Pesan awal WhatsApp', [$locale => 'Halo, saya ingin menanyakan briket arang.']],
                ['email', SettingType::Str, 'Email utama'],
                ['email_sales', SettingType::Str, 'Email sales'],
                ['office_hours', SettingType::Translatable, 'Jam operasional', [$locale => 'Senin–Jumat, 08.00–17.00 WIB']],
            ],
            'social' => [
                ['facebook_url', SettingType::Str, 'Facebook'],
                ['instagram_url', SettingType::Str, 'Instagram'],
                ['linkedin_url', SettingType::Str, 'LinkedIn'],
                ['youtube_url', SettingType::Str, 'YouTube'],
                ['tiktok_url', SettingType::Str, 'TikTok'],
                ['x_url', SettingType::Str, 'X'],
                ['alibaba_url', SettingType::Str, 'Alibaba'],
            ],
            'seo' => [
                ['default_meta_title', SettingType::Translatable, 'Meta title default', [$locale => 'Nusacarbon — Briket Arang Batok Kelapa']],
                ['default_meta_description', SettingType::Translatable, 'Meta description default', [$locale => '']],
                ['default_og_image', SettingType::Image, 'OG image default (1200×630)'],
                ['meta_keywords', SettingType::Translatable, 'Meta keywords', [$locale => '']],
                ['google_analytics_id', SettingType::Str, 'Google Analytics ID'],
                ['google_tag_manager_id', SettingType::Str, 'Google Tag Manager ID'],
                ['google_site_verification', SettingType::Str, 'Google site verification'],
                ['bing_site_verification', SettingType::Str, 'Bing site verification'],
                ['yandex_verification', SettingType::Str, 'Yandex verification'],
                ['facebook_pixel_id', SettingType::Str, 'Facebook Pixel ID'],
                ['robots_index', SettingType::Boolean, 'Izinkan mesin pencari mengindeks', true],
                ['sitemap_enabled', SettingType::Boolean, 'Aktifkan sitemap', true],
            ],
            'email' => [
                ['rfq_notification_recipients', SettingType::EmailList, 'Penerima notifikasi RFQ', 'sales@nusacarbon.com'],
                ['contact_notification_recipients', SettingType::EmailList, 'Penerima notifikasi pesan kontak', 'info@nusacarbon.com'],
                ['mail_from_name', SettingType::Str, 'Nama pengirim email', 'Nusacarbon'],
                ['mail_from_address', SettingType::Str, 'Alamat pengirim email', 'no-reply@nusacarbon.com'],
                ['send_buyer_confirmation', SettingType::Boolean, 'Kirim konfirmasi otomatis ke buyer', true],
            ],
            'system' => [
                ['active_theme_admin', SettingType::Number, 'Tema aktif panel admin', null, false],
                ['active_theme_public', SettingType::Number, 'Tema aktif halaman publik', null, false],
                ['maintenance_mode', SettingType::Boolean, 'Mode pemeliharaan', false, false],
                ['maintenance_message', SettingType::Translatable, 'Pesan pemeliharaan', [$locale => 'Situs sedang dalam pemeliharaan. Silakan kembali beberapa saat lagi.'], false],
                ['rfq_rate_limit_per_hour', SettingType::Number, 'Batas submit RFQ per IP per jam', 5, false],
                ['turnstile_site_key', SettingType::Str, 'Cloudflare Turnstile site key', null, false],
                ['turnstile_secret_key', SettingType::Str, 'Cloudflare Turnstile secret key', null, false],
            ],
        ];

        $descriptions = [
            'logo_primary' => 'Dipakai di header, footer, dan dokumen. Satu berkas untuk semua tempat.',
            'favicon' => 'Ikon tab browser. PNG atau ICO 32×32.',
            'hs_code' => 'Kode HS default yang dipakai dokumen ekspor.',
            'whatsapp' => 'Format internasional, misalnya +6281234567890.',
            'robots_index' => 'Matikan saat situs masih di staging.',
            'turnstile_secret_key' => 'Disimpan terenkripsi dan tidak pernah ditampilkan kembali.',
            'rfq_notification_recipients' => 'Boleh lebih dari satu, dipisah koma.',
            'contact_notification_recipients' => 'Boleh lebih dari satu, dipisah koma.',
        ];

        foreach ($groups as $group => $settings) {
            foreach ($settings as $position => $setting) {
                [$key, $type, $label] = $setting;
                $default = $setting[3] ?? null;
                $isPublic = $setting[4] ?? true;

                SiteSetting::query()->firstOrCreate(
                    ['key' => $key],
                    [
                        'group' => $group,
                        'label' => $label,
                        'description' => $descriptions[$key] ?? null,
                        'value' => ['data' => $default],
                        'type' => $type,
                        'sort_order' => $position,
                        'is_public' => $isPublic,
                    ],
                );
            }
        }
    }
}
