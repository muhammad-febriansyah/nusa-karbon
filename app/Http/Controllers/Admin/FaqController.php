<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Faq\CreateFaqAction;
use App\Actions\Faq\DeleteFaqAction;
use App\Actions\Faq\UpdateFaqAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Faq\StoreFaqRequest;
use App\Http\Requests\Admin\Faq\UpdateFaqRequest;
use App\Http\Resources\Admin\FaqCategoryResource;
use App\Http\Resources\Admin\FaqResource;
use App\Models\Faq;
use App\Repositories\Contracts\FaqCategoryRepositoryInterface;
use App\Repositories\Contracts\FaqRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * FAQ dikelompokkan per kategori; urutannya berlaku di dalam kategori.
 *
 * @see docs/09-tasks.md bagian 3.5
 */
final class FaqController extends Controller
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqs,
        private readonly FaqCategoryRepositoryInterface $categories,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/faqs/index', [
            'faqs' => FaqResource::collection($this->faqs->orderedWithCategory())->resolve(),
            'categories' => FaqCategoryResource::collection($this->categories->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/faqs/create', [
            'categories' => FaqCategoryResource::collection($this->categories->ordered())->resolve(),
        ]);
    }

    public function store(StoreFaqRequest $request, CreateFaqAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'FAQ berhasil ditambahkan.',
        ]);

        return to_route('admin.faqs.index');
    }

    public function edit(Faq $faq): Response
    {
        return Inertia::render('admin/faqs/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'faq' => (new FaqResource($faq))->resolve(),
            'categories' => FaqCategoryResource::collection($this->categories->ordered())->resolve(),
        ]);
    }

    public function update(
        UpdateFaqRequest $request,
        Faq $faq,
        UpdateFaqAction $action,
    ): RedirectResponse {
        $action->handle($faq, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan FAQ berhasil disimpan.',
        ]);

        return to_route('admin.faqs.index');
    }

    public function destroy(Faq $faq, DeleteFaqAction $action): RedirectResponse
    {
        $action->handle($faq);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'FAQ berhasil dihapus.',
        ]);

        return back();
    }

    /**
     * Menyimpan urutan baru di dalam satu kategori.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:faqs,id'],
        ]);

        $this->faqs->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan FAQ berhasil disimpan.',
        ]);

        return back();
    }
}
