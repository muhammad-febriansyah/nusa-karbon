<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Representative\CreateRepresentativeAction;
use App\Actions\Representative\DeleteRepresentativeAction;
use App\Actions\Representative\UpdateRepresentativeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Representative\StoreRepresentativeRequest;
use App\Http\Requests\Admin\Representative\UpdateRepresentativeRequest;
use App\Http\Resources\Admin\RepresentativeResource;
use App\Models\Representative;
use App\Repositories\Contracts\RepresentativeRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class RepresentativeController extends Controller
{
    public function __construct(
        private readonly RepresentativeRepositoryInterface $representatives,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'is_active', 'region', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/representatives/index', [
            'representatives' => RepresentativeResource::collection(
                $this->representatives->paginate($filters, $request->integer('per_page', 15)),
            ),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/representatives/create');
    }

    public function store(
        StoreRepresentativeRequest $request,
        CreateRepresentativeAction $action,
    ): RedirectResponse {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perwakilan resmi berhasil ditambahkan.',
        ]);

        return to_route('admin.representatives.index');
    }

    public function edit(Representative $representative): Response
    {
        return Inertia::render('admin/representatives/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'representative' => (new RepresentativeResource($representative))->resolve(),
        ]);
    }

    public function update(
        UpdateRepresentativeRequest $request,
        Representative $representative,
        UpdateRepresentativeAction $action,
    ): RedirectResponse {
        $action->handle($representative, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan perwakilan berhasil disimpan.',
        ]);

        return to_route('admin.representatives.index');
    }

    public function destroy(
        Representative $representative,
        DeleteRepresentativeAction $action,
    ): RedirectResponse {
        $action->handle($representative);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perwakilan resmi berhasil dihapus.',
        ]);

        return back();
    }
}
