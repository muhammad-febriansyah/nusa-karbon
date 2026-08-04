<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pencatatan unduhan dokumen dari sisi publik.
 *
 * Tanpa `updated_at` dan tanpa soft delete — barisnya hanya ditulis sekali.
 *
 * @see docs/04-erd.md bagian 3.2
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_downloads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_document_id')
                ->constrained('product_documents')
                ->cascadeOnDelete();

            $table->string('ip_address', 45)->comment('IPv6-safe');
            $table->char('country_code', 2)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer', 500)->nullable();
            $table->char('locale', 2);

            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_downloads');
    }
};
