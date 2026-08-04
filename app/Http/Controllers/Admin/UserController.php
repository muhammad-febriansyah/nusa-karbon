<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\User\CreateUserAction;
use App\Actions\User\DeleteUserAction;
use App\Actions\User\ToggleUserActiveAction;
use App\Actions\User\UpdateUserAction;
use App\Exceptions\LastActiveAdminException;
use App\Exceptions\SelfAccountActionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\ActivityResource;
use App\Http\Resources\Admin\UserResource;
use App\Models\RfqRequest;
use App\Models\User;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly ActivityLogRepositoryInterface $activities,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'is_active', 'sort_by', 'sort_dir', 'per_page',
        ]);

        return Inertia::render('admin/users/index', [
            'users' => UserResource::collection(
                $this->users->paginate($filters, $request->integer('per_page', 15)),
            ),
            // Dipakai halaman untuk menonaktifkan aksi pada akun sendiri.
            'currentUserId' => $request->user()?->id,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request, CreateUserAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pengguna berhasil dibuat. Undangan pembuatan password sudah dikirim.',
        ]);

        return to_route('admin.users.index');
    }

    public function show(User $user): Response
    {
        // Jumlah RFQ yang pernah ditangani: RFQ yang punya catatan dari
        // pengguna ini.
        $handledRfqs = RfqRequest::query()
            ->whereHas('notes', fn (Builder $query) => $query->where('user_id', $user->id))
            ->count();

        return Inertia::render('admin/users/show', [
            'user' => (new UserResource($user))->resolve(),
            'handledRfqs' => $handledRfqs,
            'activities' => ActivityResource::collection(
                $this->activities->latestByCauser($user->id),
            )->resolve(),
        ]);
    }

    public function edit(User $user): Response
    {
        return Inertia::render('admin/users/edit', [
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UpdateUserAction $action,
    ): RedirectResponse {
        $action->handle($user, $request->validated());

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Perubahan pengguna berhasil disimpan.',
        ]);

        return to_route('admin.users.index');
    }

    public function destroy(User $user, DeleteUserAction $action): RedirectResponse
    {
        try {
            $action->handle($user);
        } catch (SelfAccountActionException|LastActiveAdminException $exception) {
            return back()->withErrors(['user' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pengguna berhasil dihapus.',
        ]);

        return to_route('admin.users.index');
    }

    public function toggleActive(
        Request $request,
        User $user,
        ToggleUserActiveAction $action,
    ): RedirectResponse {
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        try {
            $action->handle($user, (bool) $validated['is_active']);
        } catch (SelfAccountActionException|LastActiveAdminException $exception) {
            return back()->withErrors(['user' => $exception->getMessage()]);
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $validated['is_active']
                ? 'Pengguna berhasil diaktifkan.'
                : 'Pengguna berhasil dinonaktifkan.',
        ]);

        return back();
    }

    /**
     * Undangan dan reset password memakai alur yang sama: tautan sekali pakai
     * ke email pemilik akun.
     */
    public function sendPasswordLink(User $user): RedirectResponse
    {
        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Tautan pembuatan password dikirim ke {$user->email}.",
        ]);

        return back();
    }
}
