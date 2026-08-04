<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Content\CreateSortableContentAction;
use App\Actions\Content\DeleteSortableContentAction;
use App\Actions\Content\UpdateSortableContentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeamMember\StoreTeamMemberRequest;
use App\Http\Requests\Admin\TeamMember\UpdateTeamMemberRequest;
use App\Http\Resources\Admin\TeamMemberResource;
use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Anggota tim memakai pola konten berurutan: daftar pendek, diurutkan manual, tanpa
 * pagination.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final class TeamMemberController extends Controller
{
    /**
     * Kolom berkas beserta direktori tujuannya.
     *
     * @var array<string, string>
     */
    private const UPLOADS = ['photo_path' => TeamMember::FILE_DIRECTORY];

    public function __construct(
        private readonly TeamMemberRepositoryInterface $items,
    ) {}

    public function index(): Response
    {
        return Inertia::render('admin/team-members/index', [
            'items' => TeamMemberResource::collection($this->items->ordered())->resolve(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/team-members/create');
    }

    public function store(
        StoreTeamMemberRequest $request,
        CreateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Anggota tim berhasil ditambahkan.',
        ]);

        return to_route('admin.team-members.index');
    }

    public function edit(TeamMember $teamMember): Response
    {
        return Inertia::render('admin/team-members/edit', [
            // `resolve()` supaya tidak terbungkus kunci `data`; halaman form
            // mengharapkan objeknya langsung.
            'item' => (new TeamMemberResource($teamMember))->resolve(),
        ]);
    }

    public function update(
        UpdateTeamMemberRequest $request,
        TeamMember $teamMember,
        UpdateSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $teamMember, $request->validated(), self::UPLOADS);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan anggota tim berhasil disimpan.',
        ]);

        return to_route('admin.team-members.index');
    }

    public function destroy(
        TeamMember $teamMember,
        DeleteSortableContentAction $action,
    ): RedirectResponse {
        $action->handle($this->items, $teamMember);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Anggota tim berhasil dihapus.',
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
            'ids.*' => ['integer', 'exists:team_members,id'],
        ]);

        $this->items->reorder($validated['ids']);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Urutan anggota tim berhasil disimpan.',
        ]);

        return back();
    }
}
