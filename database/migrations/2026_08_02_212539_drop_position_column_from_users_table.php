<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom `position` dibatalkan atas permintaan klien: manajemen pengguna hanya
 * CRUD sederhana, jabatan tidak dipakai di logika mana pun.
 *
 * Menyimpang dari docs/04-erd.md bagian 3.1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 255)->nullable()->after('phone');
        });
    }
};
