<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingPartner\StoreShippingPartnerRequest;
use App\Http\Requests\Admin\ShippingPartner\UpdateShippingPartnerRequest;
use App\Http\Resources\Admin\ShippingPartnerResource;
use App\Models\ShippingPartner;
use App\Repositories\Contracts\ShippingPartnerRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Mitra pelayaran memakai pola konten berurutan: daftar pendek, diurutkan manual, tanpa
 * pagination.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class ShippingPartnerController extends Controller
{
    /**
     * Kolom berkas beserta direktori tujuannya.
     *
     * @var array<string, string>
     */
    private const UPLOADS = ['logo_path' => ShippingPartner::FILE_DIRECTORY];

    public function __construct(
        private readonly ShippingPartnerRepositoryInterface $items,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/shipping-partners/index', [
            'items' => ShippingPartnerResource::collection($this->items->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/shipping-partners/create');
    }

    public function store(
        StoreShippingPartnerRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Mitra pelayaran berhasil ditambahkan.',
        ]);

        return to_route('admin.shipping-partners.index');
    }

    public function edit(ShippingPartner $shippingPartner): Response
    {
        return Inertia::render('admin/shipping-partners/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'item' => (new ShippingPartnerResource($shippingPartner))->resolve(),
        ]);
    }

    public function update(
        UpdateShippingPartnerRequest $request,
        ShippingPartner $shippingPartner,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $shippingPartner, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan mitra pelayaran berhasil disimpan.',
        ]);

        return to_route('admin.shipping-partners.index');
    }

    public function destroy(
        ShippingPartner $shippingPartner,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $shippingPartner);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Mitra pelayaran berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:shipping_partners,id'],
        ]);

        $this->items->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan mitra pelayaran berhasil disimpan.',
        ]);

        return back();
    }
}
