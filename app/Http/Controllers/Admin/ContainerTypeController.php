<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\ContainerType\CreateContainerTypeAction;
use App\Actions\ContainerType\DeleteContainerTypeAction;
use App\Actions\ContainerType\UpdateContainerTypeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContainerType\StoreContainerTypeRequest;
use App\Http\Requests\Admin\ContainerType\UpdateContainerTypeRequest;
use App\Http\Resources\Admin\ContainerTypeResource;
use App\Models\ContainerType;
use App\Repositories\Contracts\ContainerTypeRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ContainerTypeController extends Controller
{
    public function __construct(
        private readonly ContainerTypeRepositoryInterface $containerTypes,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'is_active', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/container-types/index', [
            'containerTypes' => ContainerTypeResource::collection(
                $this->containerTypes->paginate($filters, $request->integer('per_page', 15)),
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/container-types/create');
    }

    public function store(
        StoreContainerTypeRequest $request,
        CreateContainerTypeAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tipe kontainer berhasil ditambahkan.',
        ]);

        return to_route('admin.container-types.index');
    }

    public function edit(ContainerType $containerType): Response
    {
        return Inertia::render('admin/container-types/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'containerType' => (new ContainerTypeResource($containerType))->resolve(),
        ]);
    }

    public function update(
        UpdateContainerTypeRequest $request,
        ContainerType $containerType,
        UpdateContainerTypeAction $action,
    ): RedirectResponse {
        $action->handle($containerType, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan tipe kontainer berhasil disimpan.',
        ]);

        return to_route('admin.container-types.index');
    }

    public function destroy(
        ContainerType $containerType,
        DeleteContainerTypeAction $action,
    ): RedirectResponse {
        $action->handle($containerType);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Tipe kontainer berhasil dihapus.',
        ]);

        return back();
    }
}
