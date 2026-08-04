import { router } from '@inertiajs/react';
import { History } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import DateRangePicker from '@/components/form/date-range-picker';
import { DetailButton } from '@/components/shared/action-buttons';
import InfoDialog from '@/components/shared/info-dialog';
import PageHeader from '@/components/shared/page-header';
import StatusBadge from '@/components/shared/status-badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/admin';
import { index as activityLogIndex } from '@/routes/admin/activity-log';
import type { ActivityLogEntry, SelectOption } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const EVENT_LABELS: Record<string, string> = {
    created: 'Dibuat',
    updated: 'Diubah',
    deleted: 'Dihapus',
    restored: 'Dipulihkan',
};

const EVENT_TONES: Record<
    string,
    'success' | 'info' | 'destructive' | 'muted'
> = {
    created: 'success',
    updated: 'info',
    deleted: 'destructive',
    restored: 'muted',
};

type Props = {
    activities: Paginated<ActivityLogEntry>;
    subjectTypes: SelectOption[];
    users: SelectOption[];
    filters: TableFilters;
};

export default function ActivityLogIndex({
    activities,
    subjectTypes,
    users,
    filters,
}: Props) {
    const [detail, setDetail] = useState<ActivityLogEntry | null>(null);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            activityLogIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<ActivityLogEntry>[] = [
        {
            id: 'created_at',
            header: 'Waktu',
            cell: (activity) => (
                <span className="tabular-nums">{activity.created_at}</span>
            ),
        },
        {
            id: 'causer_name',
            header: 'Pengguna',
            cell: (activity) => activity.causer_name,
        },
        {
            id: 'subject',
            header: 'Objek',
            cell: (activity) => (
                <div className="flex flex-col leading-tight">
                    <span>{activity.subject_label ?? '—'}</span>
                    <span className="text-xs text-muted-foreground">
                        {activity.log_name}
                        {activity.subject_id !== null &&
                            ` #${activity.subject_id}`}
                    </span>
                </div>
            ),
        },
        {
            id: 'event',
            header: 'Peristiwa',
            cell: (activity) => (
                <StatusBadge
                    label={
                        EVENT_LABELS[activity.event ?? ''] ??
                        activity.event ??
                        '—'
                    }
                    tone={EVENT_TONES[activity.event ?? ''] ?? 'muted'}
                />
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (activity) => (
                <DetailButton onClick={() => setDetail(activity)} />
            ),
        },
    ];

    const changedKeys = detail
        ? [
              ...new Set([
                  ...Object.keys(detail.changes.old),
                  ...Object.keys(detail.changes.attributes),
              ]),
          ]
        : [];

    return (
        <>
            <PageHeader
                title="Log Aktivitas"
                description="Jejak perubahan data sensitif beserta nilai lama dan barunya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Log Aktivitas', href: activityLogIndex() },
                ]}
            />

            <div className="flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                <History
                    aria-hidden
                    className="mt-0.5 size-4 shrink-0 text-info"
                />
                <p className="text-[13px] text-info">
                    Log bersifat baca-saja dan tidak bisa dihapus dari sini.
                    Pembersihan otomatis menghapus catatan lebih tua dari 12
                    bulan.
                </p>
            </div>

            <DataTable
                rows={activities}
                columns={columns}
                filters={filters}
                rowId={(activity) => activity.id}
                reloadOnly={['activities', 'filters']}
                searchPlaceholder="Cari deskripsi peristiwa…"
                emptyTitle="Belum ada aktivitas"
                emptyDescription="Perubahan pada data sensitif akan tercatat di sini."
                filterSlot={
                    <>
                        <Select
                            value={
                                filters.causer_id === undefined ||
                                filters.causer_id === ''
                                    ? 'all'
                                    : String(filters.causer_id)
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'causer_id',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-44">
                                <SelectValue placeholder="Semua pengguna" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua pengguna
                                </SelectItem>
                                {users.map((user) => (
                                    <SelectItem
                                        key={user.value}
                                        value={user.value}
                                    >
                                        {user.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={
                                typeof filters.subject_type === 'string' &&
                                filters.subject_type !== ''
                                    ? filters.subject_type
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'subject_type',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-48">
                                <SelectValue placeholder="Semua objek" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua objek</SelectItem>
                                {subjectTypes.map((type) => (
                                    <SelectItem
                                        key={type.value}
                                        value={type.value}
                                    >
                                        {type.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <DateRangePicker
                            value={{
                                from:
                                    typeof filters.from === 'string'
                                        ? filters.from
                                        : null,
                                to:
                                    typeof filters.to === 'string'
                                        ? filters.to
                                        : null,
                            }}
                            onChange={(range) =>
                                router.get(
                                    activityLogIndex().url,
                                    {
                                        ...filters,
                                        from: range.from ?? undefined,
                                        to: range.to ?? undefined,
                                        page: 1,
                                    },
                                    { preserveState: true, replace: true },
                                )
                            }
                        />
                    </>
                }
            />

            <InfoDialog
                open={detail !== null}
                onOpenChange={(open) => !open && setDetail(null)}
                title={`${detail?.subject_label ?? 'Objek'} ${
                    EVENT_LABELS[detail?.event ?? ''] ?? ''
                }`}
                description={
                    <div className="space-y-3">
                        <p className="text-[13px] text-muted-foreground">
                            {detail?.causer_name ?? 'Sistem'} ·{' '}
                            {detail?.created_at ?? ''}
                        </p>

                        {changedKeys.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Peristiwa ini tidak mengubah nilai kolom mana
                                pun.
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="text-start text-xs text-muted-foreground">
                                            <th className="pb-2 text-start font-medium">
                                                Kolom
                                            </th>
                                            <th className="pb-2 text-start font-medium">
                                                Nilai lama
                                            </th>
                                            <th className="pb-2 text-start font-medium">
                                                Nilai baru
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {changedKeys.map((key) => (
                                            <tr
                                                key={key}
                                                className="border-t border-border align-top"
                                            >
                                                <td className="py-2 pe-3 font-medium">
                                                    {key}
                                                </td>
                                                <td className="py-2 pe-3 text-muted-foreground">
                                                    {formatValue(
                                                        detail?.changes.old[
                                                            key
                                                        ],
                                                    )}
                                                </td>
                                                <td className="py-2">
                                                    {formatValue(
                                                        detail?.changes
                                                            .attributes[key],
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                }
            />
        </>
    );
}

function formatValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
}
