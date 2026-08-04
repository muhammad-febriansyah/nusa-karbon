<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\PageBlock\UpdatePageBlockAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageBlock\UpdatePageBlockRequest;
use App\Http\Resources\Admin\PageBlockResource;
use App\Models\PageBlock;
use App\Repositories\Contracts\PageBlockRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Blok halaman statis hanya bisa diubah isinya. Tidak ada create maupun
 * destroy: kunci bloknya adalah kontrak dengan halaman publik dan hanya
 * berubah lewat seeder.
 *
 * @see docs/09-tasks.md bagian 3.7
 */
final class PageBlockController extends Controller
{
    public function __construct(
        private readonly PageBlockRepositoryInterface $blocks,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/page-blocks/index', [
            'blocks' => PageBlockResource::collection($this->blocks->ordered())->resolve(),
        ]);
    }

    public function edit(PageBlock $pageBlock): Response
    {
        return Inertia::render('admin/page-blocks/edit', [
            'block' => (new PageBlockResource($pageBlock))->resolve(),
        ]);
    }

    public function update(
        UpdatePageBlockRequest $request,
        PageBlock $pageBlock,
        UpdatePageBlockAction $action,
    ): RedirectResponse {
        $action->handle($pageBlock, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Isi blok halaman berhasil disimpan.',
        ]);

        return to_route('admin.page-blocks.index');
    }
}
