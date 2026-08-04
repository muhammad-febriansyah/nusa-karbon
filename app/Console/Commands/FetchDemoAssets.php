<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Demo\DemoAssetFetcher;
use Illuminate\Console\Command;

/**
 * Mengisi cache `storage/app/demo-assets/` supaya `DemoSeeder` tidak perlu
 * menunggu unduhan. Aman dijalankan berulang: berkas yang sudah ada dilewati.
 */
final class FetchDemoAssets extends Command
{
    protected $signature = 'demo:fetch-assets';

    protected $description = 'Unduh foto contoh berlisensi bebas pakai ke cache lokal';

    public function handle(DemoAssetFetcher $fetcher): int
    {
        foreach ($fetcher->warm() as $topic => $total) {
            $this->line(sprintf('%-12s %d berkas', $topic, $total));
        }

        return self::SUCCESS;
    }
}
