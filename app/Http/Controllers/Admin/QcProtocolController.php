<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QcProtocol\StoreQcProtocolRequest;
use App\Http\Requests\Admin\QcProtocol\UpdateQcProtocolRequest;
use App\Http\Resources\Admin\QcProtocolResource;
use App\Models\QcProtocol;
use App\Repositories\Contracts\QcProtocolRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Protokol QC memakai pola konten berurutan: daftar pendek, diurutkan manual, tanpa
 * pagination.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class QcProtocolController extends Controller
{
    /**
     * Kolom berkas beserta direktori tujuannya.
     *
     * @var array<string, string>
     */
    private const UPLOADS = ['image_path' => QcProtocol::FILE_DIRECTORY];

    public function __construct(
        private readonly QcProtocolRepositoryInterface $items,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/qc-protocols/index', [
            'items' => QcProtocolResource::collection($this->items->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/qc-protocols/create');
    }

    public function store(
        StoreQcProtocolRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Protokol QC berhasil ditambahkan.',
        ]);

        return to_route('admin.qc-protocols.index');
    }

    public function edit(QcProtocol $qcProtocol): Response
    {
        return Inertia::render('admin/qc-protocols/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'item' => (new QcProtocolResource($qcProtocol))->resolve(),
        ]);
    }

    public function update(
        UpdateQcProtocolRequest $request,
        QcProtocol $qcProtocol,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $qcProtocol, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan protokol QC berhasil disimpan.',
        ]);

        return to_route('admin.qc-protocols.index');
    }

    public function destroy(
        QcProtocol $qcProtocol,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $qcProtocol);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Protokol QC berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:qc_protocols,id'],
        ]);

        $this->items->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan protokol QC berhasil disimpan.',
        ]);

        return back();
    }
}
