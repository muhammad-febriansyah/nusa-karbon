<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivityResource;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Log aktivitas hanya bisa dibaca. Tidak ada route hapus: jejak audit yang
 * bisa dihapus dari UI kehilangan gunanya sebagai bukti.
 *
 * @see docs/09-tasks.md bagian 4.5
 */
final class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogRepositoryInterface $activities,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'search', 'causer_id', 'subject_type', 'event', 'from', 'to', 'per_page',
        ]);

        return Inertia::render('admin/activity-log/index', [
            'activities' => ActivityResource::collection(
                $this->activities->paginate($filters, $request->integer('per_page', 25)),
            ),
            'subjectTypes' => $this->activities->subjectTypes(),
            'users' => $this->users->options(),
            'filters' => $filters,
        ]);
    }
}
