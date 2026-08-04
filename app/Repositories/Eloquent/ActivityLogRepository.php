<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
     * @return Builder<Activity>
     */
    private function query(): Builder
    {
        return Activity::query()->with('causer:id,name');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Activity>
     */
    public function paginate(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->query();

        $search = $filters['search'] ?? null;

        if (is_string($search) && $search !== '') {
            $query->where('description', 'like', "%{$search}%");
        }

        $causer = $filters['causer_id'] ?? null;

        if ($causer !== null && $causer !== '') {
            $query->where('causer_id', (int) $causer);
        }

        $subjectType = $filters['subject_type'] ?? null;

        if (is_string($subjectType) && $subjectType !== '') {
            $query->where('subject_type', $subjectType);
        }

        $event = $filters['event'] ?? null;

        if (is_string($event) && $event !== '') {
            $query->where('event', $event);
        }

        $from = $filters['from'] ?? null;

        if (is_string($from) && $from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }

        $to = $filters['to'] ?? null;

        if (is_string($to) && $to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query->latest('id')->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function latestForSubject(string $subjectType, int $subjectId, int $limit = 20): Collection
    {
        return $this->query()
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function latestByCauser(int $userId, int $limit = 20): Collection
    {
        return $this->query()
            ->where('causer_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Activity>
     */
    public function latest(int $limit = 8): Collection
    {
        return $this->query()->latest('id')->limit($limit)->get();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function subjectTypes(): array
    {
        return Activity::query()
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::headline(class_basename($type)),
            ])
            ->values()
            ->all();
    }
}
