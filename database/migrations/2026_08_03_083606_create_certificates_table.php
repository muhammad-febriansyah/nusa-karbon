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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->string('issuer')->nullable();
            $table->string('certificate_number', 100)->nullable();
            $table->json('description')->nullable();

            $table->string('logo_path', 500)->nullable();
            $table->string('file_path', 500)->nullable()->comment('hasil scan sertifikat');

            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();

            // Sertifikat yang disorot di trust bar halaman publik.
            $table->boolean('show_in_trust_bar')->default(false);

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('expires_at');
            $table->index('show_in_trust_bar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
