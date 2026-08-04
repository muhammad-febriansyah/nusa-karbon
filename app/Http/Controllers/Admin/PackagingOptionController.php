<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\PackagingOption\CreatePackagingOptionAction;
use App\Actions\PackagingOption\DeletePackagingOptionAction;
use App\Actions\PackagingOption\UpdatePackagingOptionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackagingOption\StorePackagingOptionRequest;
use App\Http\Requests\Admin\PackagingOption\UpdatePackagingOptionRequest;
use App\Http\Resources\Admin\PackagingOptionResource;
use App\Models\PackagingOption;
use App\Repositories\Contracts\PackagingOptionRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PackagingOptionController extends Controller
{
    public function __construct(
        private readonly PackagingOptionRepositoryInterface $options,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_dir', 'per_page']);

        return Inertia::render('admin/packaging-options/index', [
            'packagingOptions' => PackagingOptionResource::collection(
                $this->options->paginate($filters, $request->integer('per_page', 15)),
            ),
            // Hanya dimuat saat admin membuka mode atur urutan, karena
            // daftarnya tidak dipaginasi.
            'orderedOptions' => Inertia::optional(
                fn () => PackagingOptionResource::collection($this->options->ordered())
                    ->resolve(),
            ),
            'filters' => $filters,
        ]);
    }

    public function store(
        StorePackagingOptionRequest $request,
        CreatePackagingOptionAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opsi kemasan berhasil ditambahkan.',
        ]);

        return back();
    }

    public function update(
        UpdatePackagingOptionRequest $request,
        PackagingOption $packagingOption,
        UpdatePackagingOptionAction $action,
    ): RedirectResponse {
        $action->handle($packagingOption, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan opsi kemasan berhasil disimpan.',
        ]);

        return back();
    }

    public function destroy(
        PackagingOption $packagingOption,
        DeletePackagingOptionAction $action,
    ): RedirectResponse {
        $action->handle($packagingOption);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Opsi kemasan berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:packaging_options,id'],
        ]);

        $this->options->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan opsi kemasan berhasil disimpan.',
        ]);

        return back();
    }
}
