<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Exceptions\IncompleteMasterCartonException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Public\CertificateResource;
use App\Http\Resources\Public\FeatureResource;
use App\Http\Resources\Public\PackagingOptionResource;
use App\Http\Resources\Public\PageBlockResource;
use App\Http\Resources\Public\PostCardResource;
use App\Http\Resources\Public\ProductCardResource;
use App\Http\Resources\Public\ProductionStepResource;
use App\Http\Resources\Public\ShippingPartnerResource;
use App\Http\Resources\Public\SpecSheetResource;
use App\Models\PageBlock;
use App\Models\Product;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use App\Repositories\Contracts\FeatureRepositoryInterface;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ProductionStepRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ShippingPartnerRepositoryInterface;
use App\Services\ContainerCalculatorService;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Beranda.
 *
 * Seluruh isinya berasal dari admin panel — tidak ada teks pemasaran yang
 * ditanam di kode. Blok naratif (hero, intro, CTA) datang dari `page_blocks`,
 * sisanya dari modul masing-masing.
 *
 * @see docs/09-tasks.md bagian 5.2
 */
final class HomeController extends Controller
{
    /** Jumlah produk yang tampil di etalase beranda. */
    private const PRODUCT_LIMIT = 6;

    /** Jumlah artikel terbaru di beranda. */
    private const POST_LIMIT = 3;

    public function __construct(
        private readonly PageBlockRepositoryInterface $pageBlocks,
        private readonly ProductRepositoryInterface $products,
        private readonly FeatureRepositoryInterface $features,
        private readonly PackagingOptionRepositoryInterface $packagingOptions,
        private readonly ShippingPartnerRepositoryInterface $shippingPartners,
        private readonly CertificateRepositoryInterface $certificates,
        private readonly PostRepositoryInterface $posts,
        private readonly ProductionStepRepositoryInterface $productionSteps,
        private readonly ContainerTypeRepositoryInterface $containers,
        private readonly ContainerCalculatorService $calculator,
    ) {}

    public function __invoke(): Response
    {
        $products = $this->products->featuredForPublic(self::PRODUCT_LIMIT);
        $reference = $this->reference($products);

        return Inertia::render('public/home', [
            'blocks' => $this->blocks(),
            'products' => ProductCardResource::collection($products)->resolve(),
            'specSheet' => $reference === null
                ? null
                : SpecSheetResource::make(
                    $reference->load('specifications'),
                )->resolve(),
            'containerLoad' => $this->containerLoad($reference),
            'features' => FeatureResource::collection(
                $this->features->activeOrdered(),
            )->resolve(),
            'productionSteps' => ProductionStepResource::collection(
                $this->productionSteps->activeOrdered(),
            )->resolve(),
            'packagingOptions' => PackagingOptionResource::collection(
                $this->packagingOptions->activeOrdered(),
            )->resolve(),
            'shippingPartners' => ShippingPartnerResource::collection(
                $this->shippingPartners->activeOrdered(),
            )->resolve(),
            'trustBar' => CertificateResource::collection(
                $this->certificates->trustBar(),
            )->resolve(),
            'latestPosts' => PostCardResource::collection(
                $this->posts->latestPublished(self::POST_LIMIT),
            )->resolve(),
        ]);
    }

    /**
     * Produk acuan beranda: unggulan pertama yang datanya cukup untuk dihitung.
     *
     * Beranda memakainya dua kali — sebagai sumber angka uji di hero dan sebagai
     * muatan pada denah kontainer — jadi produk yang master carton-nya belum
     * lengkap dilewati, bukan ditampilkan dengan kolom kosong.
     *
     * @param  Collection<int, Product>  $products
     */
    private function reference(Collection $products): ?Product
    {
        return $products->first(
            fn (Product $product): bool => $product->isCalculable(),
        );
    }

    /**
     * Estimasi muatan produk acuan pada kontainer pertama yang aktif.
     *
     * Kontainernya mengikuti urutan yang disusun admin, bukan kode yang
     * ditanam di sini, supaya "kontainer yang paling sering dipakai" bisa
     * berubah lewat panel.
     *
     * @return array<string, mixed>|null
     */
    private function containerLoad(?Product $product): ?array
    {
        $container = $this->containers->activeOrdered()->first();

        if ($product === null || $container === null) {
            return null;
        }

        try {
            $result = $this->calculator->calculate($product, $container);
        } catch (IncompleteMasterCartonException) {
            // Sudah disaring `reference()`; dijaga agar data yang tidak lengkap
            // menghilangkan satu bagian, bukan menggagalkan seluruh beranda.
            return null;
        }

        $locale = app()->getLocale();

        return [
            'container' => [
                'code' => $container->code,
                'name' => $container->getTranslation('name', $locale),
                'max_payload_kg' => (float) $container->max_payload_kg,
                'max_cbm' => (float) $container->max_cbm,
                'internal_length_m' => $container->internal_length_m === null
                    ? null
                    : (float) $container->internal_length_m,
                'internal_width_m' => $container->internal_width_m === null
                    ? null
                    : (float) $container->internal_width_m,
                'internal_height_m' => $container->internal_height_m === null
                    ? null
                    : (float) $container->internal_height_m,
            ],
            'product' => [
                'code' => $product->code,
                'name' => $product->getTranslation('name', $locale) ?: null,
                'slug' => $product->getTranslation('slug', $locale) ?: null,
                'master_carton_weight_kg' => (float) $product->master_carton_weight_kg,
            ],
            ...$result,
        ];
    }

    /**
     * Blok halaman `home`, dipetakan berdasarkan `block_key`.
     *
     * @return array<string, array<string, mixed>>
     */
    private function blocks(): array
    {
        return $this->pageBlocks->activeForPage('home')
            ->mapWithKeys(fn (PageBlock $block): array => [
                $block->block_key => PageBlockResource::make($block)->resolve(),
            ])
            ->all();
    }
}
