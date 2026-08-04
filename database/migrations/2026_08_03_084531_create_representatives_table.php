<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Perwakilan resmi menopang halaman anti-penipuan. Setiap perubahannya wajib
 * terlacak, karena itu modelnya memakai `LogsActivity` dengan `logAll()`.
 *
 * @see docs/04-erd.md bagian 3.3
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representatives', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->json('position');
            $table->string('region')->nullable()->comment('MENA, Europe, Asia');

            $table->string('email')->comment('wajib memakai domain perusahaan');
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();

            $table->string('photo_path', 500)->nullable();

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('representatives');
    }
};
