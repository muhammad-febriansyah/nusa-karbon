<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * @see docs/04-erd.md bagian 3.3
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_protocols', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();

            // Contoh: result_label "Breakage tolerance", result_value "< 2.5%".
            $table->json('result_label')->nullable();
            $table->string('result_value')->nullable();

            $table->string('image_path', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_protocols');
    }
};
