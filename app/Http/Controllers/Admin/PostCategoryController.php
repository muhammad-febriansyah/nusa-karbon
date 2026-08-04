<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PostCategory\StorePostCategoryRequest;
use App\Http\Requests\Admin\PostCategory\UpdatePostCategoryRequest;
use App\Http\Resources\Admin\PostCategoryResource;
use App\Models\PostCategory;
use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PostCategoryController extends Controller
{
    public function __construct(
        private readonly PostCategoryRepositoryInterface $categories,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/post-categories/index', [
            'items' => PostCategoryResource::collection($this->categories->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/post-categories/create');
    }

    public function store(
        StorePostCategoryRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->categories, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Kategori artikel berhasil ditambahkan.',
        ]);

        return to_route('admin.post-categories.index');
    }

    public function edit(PostCategory $postCategory): Response
    {
        return Inertia::render('admin/post-categories/edit', [
            'item' => (new PostCategoryResource($postCategory))->resolve(),
        ]);
    }

    public function update(
        UpdatePostCategoryRequest $request,
        PostCategory $postCategory,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->categories, $postCategory, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan kategori artikel berhasil disimpan.',
        ]);

        return to_route('admin.post-categories.index');
    }

    /**
     * Artikel di kategori yang dihapus tetap ada; kategorinya menjadi kosong
     * lewat `ON DELETE SET NULL`.
     */
    public function destroy(
        PostCategory $postCategory,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $count = $this->categories->countPosts($postCategory->id);

        $action->handle($this->categories, $postCategory);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $count === 0
                ? 'Kategori artikel berhasil dihapus.'
                : "Kategori artikel berhasil dihapus. {$count} artikel kini tanpa kategori.",
        ]);

        return back();
    }

    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:post_categories,id'],
        ]);

        $this->categories->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan kategori artikel berhasil disimpan.',
        ]);

        return back();
    }
}
