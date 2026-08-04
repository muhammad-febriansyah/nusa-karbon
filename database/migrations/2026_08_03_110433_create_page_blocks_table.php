<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blok konten halaman statis. Kuncinya di-seed dari awal; admin hanya
 * mengubah isinya, tidak menambah atau menghapus kunci lewat UI.
 *
 * @see docs/04-erd.md bagian 3.5
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();

            $table->string('page_key', 100)->comment('about, export_info, home');
            $table->string('block_key', 100);

            $table->string('type', 20)->default('rich_text');

            $table->json('title')->nullable();
            $table->json('content')->nullable();
            $table->string('image_path', 500)->nullable();
            $table->json('extra')->nullable()->comment('data tambahan sesuai tipe');

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['page_key', 'block_key']);
            $table->index(['page_key', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
