<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Post\CreatePostAction;
use App\Actions\Post\DeletePostAction;
use App\Actions\Post\PublishPostAction;
use App\Actions\Post\UpdatePostAction;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Post\StorePostRequest;
use App\Http\Requests\Admin\Post\UpdatePostRequest;
use App\Http\Resources\Admin\PostCategoryResource;
use App\Http\Resources\Admin\PostResource;
use App\Models\Post;
use App\Repositories\Contracts\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly PostCategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'status', 'post_category_id', 'from', 'to',
            'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/posts/index', [
            'posts' => PostResource::collection(
                $this->posts->paginate($filters, $request->integer('per_page', 15)),
            ),
            'categories' => PostCategoryResource::collection(
                $this->categories->activeOrdered(),
            )->resolve(),
            'statuses' => PostStatus::options(),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/posts/create', [
            'categories' => PostCategoryResource::collection(
                $this->categories->activeOrdered(),
            )->resolve(),
            'statuses' => PostStatus::options(),
        ]);
    }

    public function store(StorePostRequest $request, CreatePostAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Artikel berhasil disimpan.',
        ]);

        return to_route('admin.posts.index');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('admin/posts/edit', [
            'post' => (new PostResource($post->load('tags')))->resolve(),
            'categories' => PostCategoryResource::collection(
                $this->categories->activeOrdered(),
            )->resolve(),
            'statuses' => PostStatus::options(),
        ]);
    }

    public function update(
        UpdatePostRequest $request,
        Post $post,
        UpdatePostAction $action,
    ): RedirectResponse {
        $action->handle($post, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan artikel berhasil disimpan.',
        ]);

        return to_route('admin.posts.index');
    }

    public function destroy(Post $post, DeletePostAction $action): RedirectResponse
    {
        $action->handle($post);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Artikel berhasil dihapus.',
        ]);

        return back();
    }

    /**
     * Pratinjau isi artikel sebelum terbit, memakai tata letak baca yang sama
     * dengan halaman publik.
     */
    public function preview(Post $post): Response
    {
        return Inertia::render('admin/posts/preview', [
            'post' => (new PostResource($post->load(['tags', 'category', 'author'])))->resolve(),
        ]);
    }

    public function publish(Post $post, PublishPostAction $action): RedirectResponse
    {
        $action->handle($post);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Artikel berhasil diterbitkan.',
        ]);

        return back();
    }
}
