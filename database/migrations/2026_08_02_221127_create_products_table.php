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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_category_id')
                ->nullable()
                ->constrained('product_categories')
                ->nullOnDelete();

            $table->string('code', 50)->unique();

            $table->json('name');
            $table->json('slug');
            $table->json('short_description')->nullable();
            $table->json('description')->nullable();

            $table->string('shape', 100)->nullable()->comment('Cube, Hexagonal, Finger');
            $table->string('size_mm', 100)->nullable()->comment('contoh: 26 x 26 x 26');

            // Container Calculator hanya memakai produk yang punya berat dan
            // volume master carton. Lihat docs/04-erd.md catatan implementasi.
            $table->decimal('master_carton_weight_kg', 10, 3)->nullable();
            $table->decimal('master_carton_tare_kg', 10, 3)->nullable();
            $table->decimal('master_carton_cbm', 10, 4)->nullable();
            $table->decimal('master_carton_length_cm', 8, 2)->nullable();
            $table->decimal('master_carton_width_cm', 8, 2)->nullable();
            $table->decimal('master_carton_height_cm', 8, 2)->nullable();

            $table->decimal('inner_box_weight_kg', 10, 3)->nullable();
            $table->unsignedSmallInteger('inner_boxes_per_carton')->nullable();
            $table->decimal('moq_kg', 12, 2)->nullable();

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('is_featured');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
