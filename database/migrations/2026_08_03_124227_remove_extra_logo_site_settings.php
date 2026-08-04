<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Klien hanya memakai satu logo dan satu favicon. Varian lain dihapus supaya
 * halaman pengaturan tidak menuntut berkas yang tidak pernah dipakai.
 *
 * @see docs/04-erd.md bagian 3.7
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const REMOVED_KEYS = [
        'logo_secondary',
        'logo_square',
        'apple_touch_icon',
    ];

    public function up(): void
    {
        DB::table('site_settings')->whereIn('key', self::REMOVED_KEYS)->delete();
    }

    public function down(): void
    {
        // Kunci dibuat ulang oleh `SiteSettingSeeder` bila kelak diperlukan.
    }
};
