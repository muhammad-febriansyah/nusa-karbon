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
        Schema::create('shipping_partners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path', 500)->nullable();
            $table->string('website_url', 500)->nullable();
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
        Schema::dropIfExists('shipping_partners');
    }
};
