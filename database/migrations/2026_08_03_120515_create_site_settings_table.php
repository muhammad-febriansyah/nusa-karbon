<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan situs berpola key-value supaya menambah pengaturan baru cukup
 * lewat seeder, tanpa migrasi dan tanpa menyentuh komponen React.
 *
 * @see docs/04-erd.md bagian 3.7
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            $table->string('group', 50);
            $table->string('key', 100)->unique();

            $table->string('label');
            $table->string('description', 500)->nullable();

            $table->json('value')->nullable();

            // Menentukan komponen input yang dirender halaman pengaturan.
            $table->string('type', 20)->default('string');

            $table->json('rules')->nullable()->comment('aturan validasi tambahan');

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_public')->default(true);

            $table->timestamps();

            $table->index('group');
            $table->index(['group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
