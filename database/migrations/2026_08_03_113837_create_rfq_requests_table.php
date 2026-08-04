<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permintaan penawaran dari buyer. Tanpa soft delete: lead tidak boleh hilang
 * diam-diam — yang tidak relevan ditandai spam, bukan dihapus.
 *
 * @see docs/04-erd.md bagian 3.6
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_requests', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no', 30)->unique();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();

            $table->char('country_code', 2)->comment('ISO 3166-1 alpha-2');

            $table->string('incoterms', 10)->nullable();
            $table->string('port_of_destination')->nullable();

            // Sengaja VARCHAR: buyer menulis "54 MT", "3 x 40HC", atau
            // "two containers monthly". Memaksa tipe numerik akan menggagalkan
            // submission dan menghilangkan lead.
            $table->string('monthly_tonnage', 100)->nullable();

            $table->foreignId('container_type_id')
                ->nullable()
                ->constrained('container_types')
                ->nullOnDelete();

            $table->text('packaging_notes')->nullable();
            $table->text('message')->nullable();

            $table->string('status', 20)->default('new');
            $table->boolean('is_read')->default(false);

            $table->char('locale', 2);
            $table->string('source_url', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('country_code');
            $table->index('is_read');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_requests');
    }
};
