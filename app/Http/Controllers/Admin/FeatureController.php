<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Feature\StoreFeatureRequest;
use App\Http\Requests\Admin\Feature\UpdateFeatureRequest;
use App\Http\Resources\Admin\FeatureResource;
use App\Models\Feature;
use App\Repositories\Contracts\FeatureRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Keunggulan memakai pola konten berurutan: daftar pendek, diurutkan manual, tanpa
 * pagination.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class FeatureController extends Controller
{
    /**
     * Kolom berkas beserta direktori tujuannya.
     *
     * @var array<string, string>
     */
    private const UPLOADS = ['image_path' => Feature::FILE_DIRECTORY];

    public function __construct(
        private readonly FeatureRepositoryInterface $items,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/features/index', [
            'items' => FeatureResource::collection($this->items->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/features/create');
    }

    public function store(
        StoreFeatureRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Keunggulan berhasil ditambahkan.',
        ]);

        return to_route('admin.features.index');
    }

    public function edit(Feature $feature): Response
    {
        return Inertia::render('admin/features/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'item' => (new FeatureResource($feature))->resolve(),
        ]);
    }

    public function update(
        UpdateFeatureRequest $request,
        Feature $feature,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $feature, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan keunggulan berhasil disimpan.',
        ]);

        return to_route('admin.features.index');
    }

    public function destroy(
        Feature $feature,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $feature);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Keunggulan berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:features,id'],
        ]);

        $this->items->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan keunggulan berhasil disimpan.',
        ]);

        return back();
    }
}
