<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductionStep\StoreProductionStepRequest;
use App\Http\Requests\Admin\ProductionStep\UpdateProductionStepRequest;
use App\Http\Resources\Admin\ProductionStepResource;
use App\Models\ProductionStep;
use App\Repositories\Contracts\ProductionStepRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Proses produksi memakai pola konten berurutan: daftar pendek, diurutkan manual, tanpa
 * pagination.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class ProductionStepController extends Controller
{
    /**
     * Kolom berkas beserta direktori tujuannya.
     *
     * @var array<string, string>
     */
    private const UPLOADS = ['image_path' => ProductionStep::FILE_DIRECTORY];

    public function __construct(
        private readonly ProductionStepRepositoryInterface $items,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/production-steps/index', [
            'items' => ProductionStepResource::collection($this->items->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/production-steps/create');
    }

    public function store(
        StoreProductionStepRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Proses produksi berhasil ditambahkan.',
        ]);

        return to_route('admin.production-steps.index');
    }

    public function edit(ProductionStep $productionStep): Response
    {
        return Inertia::render('admin/production-steps/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'item' => (new ProductionStepResource($productionStep))->resolve(),
        ]);
    }

    public function update(
        UpdateProductionStepRequest $request,
        ProductionStep $productionStep,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $productionStep, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan proses produksi berhasil disimpan.',
        ]);

        return to_route('admin.production-steps.index');
    }

    public function destroy(
        ProductionStep $productionStep,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $productionStep);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Proses produksi berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:production_steps,id'],
        ]);

        $this->items->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan proses produksi berhasil disimpan.',
        ]);

        return back();
    }
}
