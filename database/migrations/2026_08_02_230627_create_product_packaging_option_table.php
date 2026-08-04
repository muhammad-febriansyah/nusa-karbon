<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot murni: tanpa id sendiri dan tanpa timestamps.
 *
 * @see docs/04-erd.md bagian 3.2
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_packaging_option', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('packaging_option_id')
                ->constrained('packaging_options')
                ->cascadeOnDelete();

            $table->primary(['product_id', 'packaging_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_packaging_option');
    }
};
