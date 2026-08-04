<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan tindak lanjut RFQ, tampil sebagai timeline di halaman detail.
 *
 * @see docs/04-erd.md bagian 3.6
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rfq_request_id')
                ->constrained('rfq_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('note');

            $table->timestamps();

            $table->index(['rfq_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_notes');
    }
};
