<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melengkapi tabel `users` sesuai docs/04-erd.md bagian 3.1.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');

            $table->string('avatar_path', 255)->nullable()->after('two_factor_confirmed_at');
            $table->string('phone', 50)->nullable()->after('avatar_path');
            $table->string('position', 255)->nullable()->after('phone');

            $table->timestamp('last_login_at')->nullable()->after('position');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            $table->boolean('is_active')->default(true)->after('last_login_ip');

            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropSoftDeletes();

            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'avatar_path',
                'phone',
                'position',
                'last_login_at',
                'last_login_ip',
                'is_active',
            ]);
        });
    }
};
