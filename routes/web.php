<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminShellController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContainerTypeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\PackagingOptionController;
use App\Http\Controllers\Admin\PageBlockController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductionStepController;
use App\Http\Controllers\Admin\QcProtocolController;
use App\Http\Controllers\Admin\RepresentativeController;
use App\Http\Controllers\Admin\RfqRequestController;
use App\Http\Controllers\Admin\ShippingPartnerController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TempUploadController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Seluruh halaman admin berada di bawah prefix `/admin`.
 *
 * @see docs/06-architecture.md bagian 3
 */
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::get('search', [AdminShellController::class, 'search'])
            ->middleware('throttle:60,1')
            ->name('search');

        Route::get('notifications', [AdminShellController::class, 'notifications'])
            ->name('notifications');

        Route::post('uploads/temp', [TempUploadController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('uploads.temp.store');

        Route::delete('uploads/temp/{token}', [TempUploadController::class, 'destroy'])
            ->name('uploads.temp.destroy');

        Route::post('product-categories/reorder', [ProductCategoryController::class, 'reorder'])
            ->name('product-categories.reorder');

        Route::resource('product-categories', ProductCategoryController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('products/bulk', [ProductController::class, 'bulk'])
            ->name('products.bulk');

        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])
            ->name('products.duplicate');

        Route::resource('products', ProductController::class);

        Route::resource('container-types', ContainerTypeController::class)
            ->except(['show']);

        Route::post('packaging-options/reorder', [PackagingOptionController::class, 'reorder'])
            ->name('packaging-options.reorder');

        Route::resource('packaging-options', PackagingOptionController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::resource('certificates', CertificateController::class)
            ->except(['show']);

        Route::resource('representatives', RepresentativeController::class)
            ->except(['show']);

        Route::post('production-steps/reorder', [ProductionStepController::class, 'reorder'])
            ->name('production-steps.reorder');

        Route::resource('production-steps', ProductionStepController::class)
            ->except(['show']);

        Route::post('qc-protocols/reorder', [QcProtocolController::class, 'reorder'])
            ->name('qc-protocols.reorder');

        Route::resource('qc-protocols', QcProtocolController::class)
            ->except(['show']);

        Route::post('features/reorder', [FeatureController::class, 'reorder'])
            ->name('features.reorder');

        Route::resource('features', FeatureController::class)
            ->except(['show']);

        Route::post('shipping-partners/reorder', [ShippingPartnerController::class, 'reorder'])
            ->name('shipping-partners.reorder');

        Route::resource('shipping-partners', ShippingPartnerController::class)
            ->except(['show']);

        Route::post('team-members/reorder', [TeamMemberController::class, 'reorder'])
            ->name('team-members.reorder');

        Route::resource('team-members', TeamMemberController::class)
            ->except(['show']);

        Route::put('users/{user}/active', [UserController::class, 'toggleActive'])
            ->name('users.active');

        Route::post('users/{user}/password-link', [UserController::class, 'sendPasswordLink'])
            ->name('users.password-link');

        Route::resource('users', UserController::class);

        Route::post('themes/{theme}/duplicate', [ThemeController::class, 'duplicate'])
            ->name('themes.duplicate');

        Route::put('themes/{theme}/activate', [ThemeController::class, 'activate'])
            ->name('themes.activate');

        Route::resource('themes', ThemeController::class)
            ->except(['show']);

        Route::get('media', [MediaLibraryController::class, 'index'])
            ->name('media.index');

        Route::get('media/picker', [MediaLibraryController::class, 'picker'])
            ->name('media.picker');

        Route::delete('media', [MediaLibraryController::class, 'destroy'])
            ->name('media.destroy');

        Route::delete('media/orphans', [MediaLibraryController::class, 'pruneOrphans'])
            ->name('media.orphans');
        Route::get('site-settings', [SiteSettingController::class, 'index'])
            ->name('site-settings.index');

        Route::put('site-settings', [SiteSettingController::class, 'update'])
            ->name('site-settings.update');
        Route::get('activity-log', [ActivityLogController::class, 'index'])
            ->name('activity-log.index');
        Route::get('rfqs/export', [RfqRequestController::class, 'export'])
            ->name('rfqs.export');

        Route::get('rfqs', [RfqRequestController::class, 'index'])
            ->name('rfqs.index');

        Route::get('rfqs/{rfq}', [RfqRequestController::class, 'show'])
            ->name('rfqs.show');

        Route::put('rfqs/{rfq}/status', [RfqRequestController::class, 'updateStatus'])
            ->name('rfqs.status');

        Route::post('rfqs/{rfq}/notes', [RfqRequestController::class, 'storeNote'])
            ->name('rfqs.notes.store');

        Route::put('rfqs/{rfq}/spam', [RfqRequestController::class, 'markAsSpam'])
            ->name('rfqs.spam');

        Route::put('rfqs/{rfq}/unread', [RfqRequestController::class, 'markAsUnread'])
            ->name('rfqs.unread');

        Route::resource('contact-messages', ContactMessageController::class)
            ->only(['index', 'show', 'update', 'destroy']);

        Route::post('post-categories/reorder', [PostCategoryController::class, 'reorder'])
            ->name('post-categories.reorder');

        Route::resource('post-categories', PostCategoryController::class)
            ->except(['show']);

        Route::get('posts/{post}/preview', [PostController::class, 'preview'])
            ->name('posts.preview');

        Route::post('posts/{post}/publish', [PostController::class, 'publish'])
            ->name('posts.publish');

        Route::resource('posts', PostController::class)
            ->except(['show']);

        Route::post('faq-categories/reorder', [FaqCategoryController::class, 'reorder'])
            ->name('faq-categories.reorder');

        Route::resource('faq-categories', FaqCategoryController::class)
            ->except(['show']);

        Route::post('faqs/reorder', [FaqController::class, 'reorder'])
            ->name('faqs.reorder');

        Route::resource('faqs', FaqController::class)
            ->except(['show']);

        Route::post('galleries/reorder', [GalleryController::class, 'reorder'])
            ->name('galleries.reorder');

        Route::get('galleries/{gallery}/items', [GalleryController::class, 'editItems'])
            ->name('galleries.items.edit');

        Route::put('galleries/{gallery}/items', [GalleryController::class, 'syncItems'])
            ->name('galleries.items.sync');

        Route::resource('galleries', GalleryController::class)
            ->except(['show']);

        // Blok halaman hanya bisa diubah isinya; kuncinya berasal dari seeder.
        Route::resource('page-blocks', PageBlockController::class)
            ->only(['index', 'edit', 'update']);
    });

require __DIR__.'/settings.php';

/*
 * Website publik dimuat terakhir supaya rute admin dan autentikasi selalu
 * dicocokkan lebih dulu.
 *
 * Fase 6 membungkus baris ini dengan grup prefix locale; isinya tidak berubah.
 * Lihat docs/08-multilingual.md bagian 2.
 */
require __DIR__.'/public.php';
