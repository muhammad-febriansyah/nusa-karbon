<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Palet warna yang bisa dikustomisasi admin, berlaku terpisah untuk panel
 * admin dan website publik.
 *
 * @see docs/04-erd.md bagian themes
 * @see docs/10-theming.md
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100);
            $table->string('slug', 100)->unique();

            $table->string('scope', 10)->default('both')->comment('admin, public, both');

            $table->json('tokens');

            $table->boolean('is_preset')->default(false);
            $table->boolean('is_active_admin')->default(false);
            $table->boolean('is_active_public')->default(false);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('scope');
            $table->index('is_active_admin');
            $table->index('is_active_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
