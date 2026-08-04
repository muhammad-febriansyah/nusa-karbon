<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Isi demo lengkap: 30 baris per modul beserta foto dan berkasnya.
 *
 * **Bukan bagian dari `DatabaseSeeder`.** Dijalankan manual dengan
 * `php artisan db:seed --class=DemoSeeder` saat panel perlu diperlihatkan.
 * Foto diambil lewat `php artisan demo:fetch-assets`; tanpa cache itu seeder
 * tetap jalan, hanya kolom gambarnya kosong.
 *
 * Tabel `users` sengaja tidak disentuh — akun dibuat manual.
 */
final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            // Ditulis ke log, bukan konsol: penjaganya harus tetap bekerja saat
            // seeder dipanggil dari kode yang tidak punya objek Command.
            logger()->warning('DemoSeeder dilewati: data contoh tidak boleh masuk produksi.');

            return;
        }

        $this->call([
            DemoCatalogSeeder::class,
            DemoCompanySeeder::class,
            DemoContentSeeder::class,
            DemoInboxSeeder::class,
            DemoMediaBackfillSeeder::class,
        ]);
    }
}
