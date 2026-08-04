<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Activitylog\Models\Activity;

/**
 * Membersihkan catatan aktivitas yang lebih tua dari ambang simpan.
 *
 * @see docs/09-tasks.md bagian 4.5
 */
final class PruneActivityLog extends Command
{
    protected $signature = 'activity-log:prune {--months=12 : Umur maksimal catatan dalam bulan}';

    protected $description = 'Menghapus catatan activity log yang lebih tua dari batas simpan';

    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));
        $cutoff = now()->subMonths($months);

        $deleted = Activity::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("{$deleted} catatan aktivitas lebih tua dari {$months} bulan dihapus.");

        return self::SUCCESS;
    }
}
