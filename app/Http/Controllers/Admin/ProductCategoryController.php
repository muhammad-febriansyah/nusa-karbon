<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ProductCategory\CreateProductCategoryAction;
use App\Actions\ProductCategory\DeleteProductCategoryAction;
use App\Actions\ProductCategory\UpdateProductCategoryAction;
use App\Exceptions\CategoryInUseException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\Admin\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\Admin\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_dir', 'per_page']);

        return Inertia::render('admin/product-categories/index', [
            'categories' => ProductCategoryResource::collection(
                $this->categories->paginate($filters, $request->integer('per_page', 15)),
            ),
            'filters' => $filters,
        ]);
    }

    public function store(
        StoreProductCategoryRequest $request,
        CreateProductCategoryAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori produk berhasil ditambahkan.',
        ]);

        return back();
    }

    public function update(
        UpdateProductCategoryRequest $request,
        ProductCategory $productCategory,
        UpdateProductCategoryAction $action,
    ): RedirectResponse {
        $action->handle($productCategory, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan kategori produk berhasil disimpan.',
        ]);

        return back();
    }

    public function destroy(
        ProductCategory $productCategory,
        DeleteProductCategoryAction $action,
    ): RedirectResponse {
        try {
            $action->handle($productCategory);
        } catch (CategoryInUseException $exception) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $exception->getMessage(),
            ]);

            return back();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori produk berhasil dihapus.',
        ]);

        return back();
    }

    /**
     * Menyimpan urutan baru hasil drag-and-drop.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:product_categories,id'],
        ]);

        $this->categories->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan kategori berhasil disimpan.',
        ]);

        return back();
    }
}
