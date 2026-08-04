<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Gallery\CreateGalleryAction;
use App\Actions\Gallery\DeleteGalleryAction;
use App\Actions\Gallery\SyncGalleryItemsAction;
use App\Actions\Gallery\UpdateGalleryAction;
use App\Enums\GalleryItemType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gallery\StoreGalleryRequest;
use App\Http\Requests\Admin\Gallery\SyncGalleryItemsRequest;
use App\Http\Requests\Admin\Gallery\UpdateGalleryRequest;
use App\Http\Resources\Admin\GalleryResource;
use App\Models\Gallery;
use App\Repositories\Contracts\GalleryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GalleryController extends Controller
{
    public function __construct(
        private readonly GalleryRepositoryInterface $galleries,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/galleries/index', [
            'items' => GalleryResource::collection($this->galleries->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/galleries/create');
    }

    public function store(
        StoreGalleryRequest $request,
        CreateGalleryAction $action,
    ): RedirectResponse {
        $gallery = $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Album berhasil dibuat. Tambahkan foto dan videonya sekarang.',
        ]);

        return to_route('admin.galleries.items.edit', $gallery);
    }

    public function edit(Gallery $gallery): Response
    {
        return Inertia::render('admin/galleries/edit', [
            'gallery' => (new GalleryResource($gallery))->resolve(),
        ]);
    }

    public function update(
        UpdateGalleryRequest $request,
        Gallery $gallery,
        UpdateGalleryAction $action,
    ): RedirectResponse {
        $action->handle($gallery, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan album berhasil disimpan.',
        ]);

        return to_route('admin.galleries.index');
    }

    public function destroy(
        Gallery $gallery,
        DeleteGalleryAction $action,
    ): RedirectResponse {
        $action->handle($gallery);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Album berhasil dihapus.',
        ]);

        return back();
    }

    /**
     * Halaman kelola isi album: unggah foto, tambah video, atur caption dan
     * urutan.
     */
    public function editItems(Gallery $gallery): Response
    {
        return Inertia::render('admin/galleries/items', [
            'gallery' => (new GalleryResource($this->galleries->withItems($gallery)))->resolve(),
            'itemTypes' => GalleryItemType::options(),
        ]);
    }

    public function syncItems(
        SyncGalleryItemsRequest $request,
        Gallery $gallery,
        SyncGalleryItemsAction $action,
    ): RedirectResponse {
        /** @var list<array<string, mixed>> $items */
        $items = $request->validated('items', []);

        $action->handle($gallery, $items);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Isi album berhasil disimpan.',
        ]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:galleries,id'],
        ]);

        $this->galleries->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan album berhasil disimpan.',
        ]);

        return back();
    }
}
