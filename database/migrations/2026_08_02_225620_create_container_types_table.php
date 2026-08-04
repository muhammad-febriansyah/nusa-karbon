<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see docs/04-erd.md bagian 3.2
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('container_types', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique()->comment('contoh: 20FT_DV');
            $table->json('name');

            $table->decimal('max_payload_kg', 10, 2);
            $table->decimal('max_cbm', 10, 2);

            $table->decimal('internal_length_m', 6, 2)->nullable();
            $table->decimal('internal_width_m', 6, 2)->nullable();
            $table->decimal('internal_height_m', 6, 2)->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('container_types');
    }
};
