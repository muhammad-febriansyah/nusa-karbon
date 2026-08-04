<?php

declare(strict_types=1);

use App\Enums\DocumentType;
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
        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->enum('type', array_column(DocumentType::cases(), 'value'));
            $table->json('title');

            $table->string('file_path', 500);
            $table->string('file_name')->comment('nama tampilan');
            $table->unsignedBigInteger('file_size')->comment('byte');
            $table->string('mime_type', 100);

            $table->string('batch_reference', 100)->nullable()->comment('contoh: SP-2409');
            $table->date('issued_at')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_documents');
    }
};
