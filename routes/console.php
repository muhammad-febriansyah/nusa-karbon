<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Artikel terjadwal dicek tiap menit supaya waktu tayang yang diatur redaksi
// tidak meleset jauh.
Schedule::command('posts:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

// Catatan aktivitas disimpan 12 bulan; lebih dari itu hanya membebani tabel
// tanpa dipakai audit.
Schedule::command('activity-log:prune')->monthlyOn(1, '02:00');
