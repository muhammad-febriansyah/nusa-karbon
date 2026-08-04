<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Actions\FaqCategory\DeleteFaqCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqCategory\StoreFaqCategoryRequest;
use App\Http\Requests\Admin\FaqCategory\UpdateFaqCategoryRequest;
use App\Http\Resources\Admin\FaqCategoryResource;
use App\Models\FaqCategory;
use App\Repositories\Contracts\FaqCategoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class FaqCategoryController extends Controller
{
    public function __construct(
        private readonly FaqCategoryRepositoryInterface $categories,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/faq-categories/index', [
            'items' => FaqCategoryResource::collection(
                $this->categories->ordered()->loadCount('faqs'),
            )->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/faq-categories/create');
    }

    public function store(
        StoreFaqCategoryRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->categories, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori FAQ berhasil ditambahkan.',
        ]);

        return to_route('admin.faq-categories.index');
    }

    public function edit(FaqCategory $faqCategory): Response
    {
        return Inertia::render('admin/faq-categories/edit', [
            'item' => (new FaqCategoryResource($faqCategory))->resolve(),
        ]);
    }

    public function update(
        UpdateFaqCategoryRequest $request,
        FaqCategory $faqCategory,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->categories, $faqCategory, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan kategori FAQ berhasil disimpan.',
        ]);

        return to_route('admin.faq-categories.index');
    }

    /**
     * Kategori yang dihapus melepas FAQ-nya ke kelompok "Lainnya", bukan ikut
     * menghapus pertanyaannya.
     */
    public function destroy(
        FaqCategory $faqCategory,
        DeleteFaqCategoryAction $action,
    ): RedirectResponse {
        $count = $action->handle($faqCategory);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $count === 0
                ? 'Kategori FAQ berhasil dihapus.'
                : "Kategori FAQ berhasil dihapus. {$count} pertanyaan kini masuk kelompok Lainnya.",
        ]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:faq_categories,id'],
        ]);

        $this->categories->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan kategori FAQ berhasil disimpan.',
        ]);

        return back();
    }
}
