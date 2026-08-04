<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\ContactMessageRepositoryInterface;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Repositories\Contracts\FaqCategoryRepositoryInterface;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Repositories\Contracts\FeatureRepositoryInterface;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Contracts\ProductionStepRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\QcProtocolRepositoryInterface;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use App\Repositories\Contracts\ShippingPartnerRepositoryInterface;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use App\Repositories\Contracts\ThemeRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ActivityLogRepository;
use App\Repositories\Eloquent\CertificateRepository;
use App\Repositories\Eloquent\ContactMessageRepository;
use App\Repositories\Eloquent\ContainerTypeRepository;
use App\Repositories\Eloquent\FaqCategoryRepository;
use App\Repositories\Eloquent\FaqRepository;
use App\Repositories\Eloquent\FeatureRepository;
use App\Repositories\Eloquent\GalleryRepository;
use App\Repositories\Eloquent\PackagingOptionRepository;
use App\Repositories\Eloquent\PageBlockRepository;
use App\Repositories\Eloquent\PostCategoryRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\ProductCategoryRepository;
use App\Repositories\Eloquent\ProductionStepRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\QcProtocolRepository;
use App\Repositories\Eloquent\RepresentativeRepository;
use App\Repositories\Eloquent\RfqRequestRepository;
use App\Repositories\Eloquent\ShippingPartnerRepository;
use App\Repositories\Eloquent\TeamMemberRepository;
use App\Repositories\Eloquent\ThemeRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Mengikat setiap interface repository ke implementasi Eloquent-nya.
 * Tambahkan entri baru di sini saat modul baru dibuat.
 *
 * Laravel membaca properti `$bindings` ini otomatis, jadi tidak perlu
 * memanggil `bind()` manual di `register()`.
 *
 * @see docs/06-architecture.md bagian 5.2
 */
final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public $bindings = [
        ProductCategoryRepositoryInterface::class => ProductCategoryRepository::class,
        ProductRepositoryInterface::class => ProductRepository::class,
        ContainerTypeRepositoryInterface::class => ContainerTypeRepository::class,
        PackagingOptionRepositoryInterface::class => PackagingOptionRepository::class,
        CertificateRepositoryInterface::class => CertificateRepository::class,
        RepresentativeRepositoryInterface::class => RepresentativeRepository::class,
        FeatureRepositoryInterface::class => FeatureRepository::class,
        ProductionStepRepositoryInterface::class => ProductionStepRepository::class,
        QcProtocolRepositoryInterface::class => QcProtocolRepository::class,
        ShippingPartnerRepositoryInterface::class => ShippingPartnerRepository::class,
        TeamMemberRepositoryInterface::class => TeamMemberRepository::class,
        FaqCategoryRepositoryInterface::class => FaqCategoryRepository::class,
        FaqRepositoryInterface::class => FaqRepository::class,
        PageBlockRepositoryInterface::class => PageBlockRepository::class,
        GalleryRepositoryInterface::class => GalleryRepository::class,
        PostRepositoryInterface::class => PostRepository::class,
        PostCategoryRepositoryInterface::class => PostCategoryRepository::class,
        RfqRequestRepositoryInterface::class => RfqRequestRepository::class,
        ContactMessageRepositoryInterface::class => ContactMessageRepository::class,
        ActivityLogRepositoryInterface::class => ActivityLogRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        ThemeRepositoryInterface::class => ThemeRepository::class,
    ];
}
