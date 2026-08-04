<?php

declare(strict_types=1);

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\ContainerCalculatorController;
use App\Http\Controllers\Public\ExportInfoController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PostController;
use App\Http\Controllers\Public\ProductController;
use App\Http\Controllers\Public\RepresentativeController;
use Illuminate\Support\Facades\Route;

/*
 * Website publik.
 *
 * Berkas terpisah karena Fase 6 akan membungkus seluruh grup ini dengan prefix
 * locale (`/ar/...`, `/ru/...`) — lihat docs/08-multilingual.md bagian 2. Saat
 * itu tiba, yang berubah hanya pembungkusnya di `web.php`, bukan berkas ini.
 */
Route::name('public.')->group(function (): void {
    Route::get('/', HomeController::class)->name('home');

    Route::get('about', AboutController::class)->name('about');

    Route::get('products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('products/{slug}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::get('container-calculator', ContainerCalculatorController::class)
        ->name('container-calculator');

    Route::get('export-info', ExportInfoController::class)
        ->name('export-info');

    Route::get('blog', [PostController::class, 'index'])
        ->name('blog.index');

    Route::get('blog/{slug}', [PostController::class, 'show'])
        ->name('blog.show');

    Route::get('contact', [ContactController::class, 'show'])->name('contact');

    Route::post('contact', [ContactController::class, 'store'])
        ->name('contact.store');

    Route::get('verify-representative', RepresentativeController::class)
        ->name('verify-representative');
});
