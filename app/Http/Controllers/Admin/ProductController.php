<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\DuplicateProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Enums\DocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\Admin\RfqRequestResource;
use App\Models\PackagingOption;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\ContainerLoadService;
use App\Support\Locale;
use App\Support\SpecificationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly PackagingOptionRepositoryInterface $packagingOptions,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'product_category_id', 'is_active', 'missing_carton',
            'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/products/index', [
            'products' => ProductResource::collection(
                $this->products->paginate($filters, $request->integer('per_page', 15)),
            ),
            'categories' => $this->categoryOptions(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/create', [
            'categories' => $this->categoryOptions(),
            'specificationTemplate' => SpecificationTemplate::standard(),
            'documentTypes' => DocumentType::options(),
            'packagingOptions' => $this->packagingOptionChoices(),
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProductAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produk berhasil ditambahkan.',
        ]);

        return to_route('admin.products.index');
    }

    public function show(Product $product, ContainerLoadService $loads): Response
    {
        $product->load([
            'category', 'media', 'specifications', 'documents', 'packagingOptions',
        ]);

        $locale = Locale::default();

        return Inertia::render('admin/products/show', [
            'product' => (new ProductResource($product))->resolve(),
            'packagingOptions' => $product->packagingOptions
                ->map(fn (PackagingOption $option): array => [
                    'id' => $option->id,
                    'name' => $option->getTranslation('name', $locale),
                ])
                ->values()
                ->all(),
            'containerLoads' => $loads->forProduct($product),
            'rfqs' => RfqRequestResource::collection(
                $product->rfqRequests()->latest()->limit(5)->get(),
            )->resolve(),
            'rfqTotal' => $product->rfqRequests()->count(),
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['category', 'media', 'specifications', 'documents', 'packagingOptions']);

        return Inertia::render('admin/products/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objek produk langsung.
            'product' => (new ProductResource($product))->resolve(),
            'categories' => $this->categoryOptions(),
            'specificationTemplate' => SpecificationTemplate::standard(),
            'documentTypes' => DocumentType::options(),
            'packagingOptions' => $this->packagingOptionChoices(),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $action,
    ): RedirectResponse {
        $action->handle($product, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan produk berhasil disimpan.',
        ]);

        return to_route('admin.products.index');
    }

    public function destroy(
        Product $product,
        DeleteProductAction $action,
    ): RedirectResponse {
        $action->handle($product);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produk berhasil dihapus.',
        ]);

        return back();
    }

    public function duplicate(
        Product $product,
        DuplicateProductAction $action,
    ): RedirectResponse {
        $copy = $action->handle($product);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Produk berhasil diduplikat. Salinan dibuat nonaktif — periksa dulu sebelum diaktifkan.',
        ]);

        return to_route('admin.products.edit', $copy);
    }

    /**
     * Aksi massal: aktifkan, nonaktifkan, atau arsipkan.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:activate,deactivate,archive'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
        ]);

        /** @var array<int, int> $ids */
        $ids = $validated['ids'];

        $message = match ($validated['action']) {
            'activate' => $this->products->setActiveStatus($ids, true).' produk diaktifkan.',
            'deactivate' => $this->products->setActiveStatus($ids, false).' produk dinonaktifkan.',
            default => $this->products->deleteMany($ids).' produk dipindahkan ke arsip.',
        };

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }

    /**
     * Opsi kemasan aktif untuk multi-select di form produk.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function packagingOptionChoices(): array
    {
        return $this->packagingOptions->activeOrdered()
            ->map(fn (PackagingOption $option): array => [
                'value' => (string) $option->id,
                'label' => $option->getTranslation('name', Locale::default()),
            ])
            ->values()
            ->all();
    }

    /**
     * Kategori untuk `ComboboxSelect`; hanya kolom yang dipakai.
     *
     * @return array<int, array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return ProductCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->map(fn (ProductCategory $category): array => [
                'value' => (string) $category->id,
                'label' => $category->getTranslation('name', Locale::default()),
            ])
            ->values()
            ->all();
    }
}
