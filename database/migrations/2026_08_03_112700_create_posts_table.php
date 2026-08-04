<?php

declare(strict_types=1);

use App\Support\Locale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * @see docs/04-erd.md bagian 3.4
 */
return new class extends Migration
{
    public function up(): void
    {
        $locale = Locale::default();

        Schema::create('posts', function (Blueprint $table) use ($locale) {
            $table->id();

            $table->foreignId('post_category_id')
                ->nullable()
                ->constrained('post_categories')
                ->nullOnDelete();

            // Penulis artikel; dilepas menjadi null bila akunnya dihapus.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('content')->nullable();

            $table->string('cover_path', 500)->nullable();

            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();

            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();

            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedTinyInteger('reading_minutes')->nullable();

            // Kolom turunan judul locale utama supaya pencarian judul memakai
            // index biasa, bukan memindai seluruh JSON tiap baris. MySQL
            // mengembalikan nilai JSON yang masih berkutip sehingga perlu
            // `json_unquote`; SQLite tidak mengenal fungsi itu.
            $expression = DB::getDriverName() === 'sqlite'
                ? "json_extract(title, '$.\"{$locale}\"')"
                : "json_unquote(json_extract(`title`, '$.\"{$locale}\"'))";

            $table->string('search_title')->nullable()->virtualAs($expression);

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('published_at');
            $table->index(['status', 'published_at']);
            $table->index('search_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
