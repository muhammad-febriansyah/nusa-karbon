<?php

declare(strict_types=1);

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Mengisi `created_by` dan `updated_by` otomatis, sehingga Action tidak perlu
 * menuliskannya satu per satu.
 *
 * @see docs/06-architecture.md bagian 6
 */
final class BlameableObserver
{
    /**
     * Cache daftar kolom per tabel agar tidak query skema di setiap penyimpanan.
     *
     * @var array<string, array<int, string>>
     */
    private static array $columns = [];

    public function creating(Model $model): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            return;
        }

        if ($this->hasColumn($model, 'created_by') && $model->getAttribute('created_by') === null) {
            $model->setAttribute('created_by', $userId);
        }

        if ($this->hasColumn($model, 'updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }
    }

    public function updating(Model $model): void
    {
        $userId = Auth::id();

        if ($userId === null) {
            return;
        }

        if ($this->hasColumn($model, 'updated_by')) {
            $model->setAttribute('updated_by', $userId);
        }
    }

    /**
     * Observer ini dipasang lewat `#[ObservedBy]` pada model yang punya kolom
     * audit, tapi pengecekan tetap dilakukan agar seeder dan model tanpa kolom
     * tersebut tidak menabrak error kolom tidak dikenal.
     */
    private function hasColumn(Model $model, string $column): bool
    {
        $key = ($model->getConnectionName() ?? 'default').':'.$model->getTable();

        self::$columns[$key] ??= Schema::connection($model->getConnectionName())
            ->getColumnListing($model->getTable());

        return in_array($column, self::$columns[$key], true);
    }

    /**
     * Dipakai pengujian yang membuat tabel di tengah jalan.
     */
    public static function flushColumnCache(): void
    {
        self::$columns = [];
    }
}
