<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ActivityResource;
use App\Http\Resources\Admin\RfqRequestResource;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\RfqRequestRepositoryInterface;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly RfqRequestRepositoryInterface $rfqs,
        private readonly ActivityLogRepositoryInterface $activities,
    ) {}

    public function __invoke(): Response
    {
        $summary = $this->dashboard->summary();

        return Inertia::render('admin/dashboard/index', [
            'stats' => $summary['stats'],
            'monthlyRfqs' => $summary['monthlyRfqs'],
            'topCountries' => $summary['topCountries'],
            'topProducts' => $summary['topProducts'],
            'funnel' => $summary['funnel'],
            'conversion' => $summary['conversion'],
            'content' => $summary['content'],
            'attention' => $summary['attention'],
            'recentRfqs' => RfqRequestResource::collection(
                $this->rfqs->paginate([], 8)->items(),
            )->resolve(),
            'recentActivities' => ActivityResource::collection(
                $this->activities->latest(8),
            )->resolve(),
        ]);
    }
}
