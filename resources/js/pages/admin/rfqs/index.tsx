import { Link, router } from '@inertiajs/react';
import { Download, Eye, Inbox } from 'lucide-react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import DateRangePicker from '@/components/form/date-range-picker';
import PageHeader from '@/components/shared/page-header';
import StatusBadge from '@/components/shared/status-badge';
import type { StatusTone } from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import {
    exportMethod as exportRfqs,
    index as rfqsIndex,
    show as rfqShow,
} from '@/routes/admin/rfqs';
import type { RfqRequest, SelectOption } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

type Props = {
    rfqs: Paginated<RfqRequest>;
    statuses: SelectOption[];
    unreadCount: number;
    filters: TableFilters;
};

export default function RfqIndex({
    rfqs,
    statuses,
    unreadCount,
    filters,
}: Props) {
    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            rfqsIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<RfqRequest>[] = [
        {
            id: 'reference_no',
            header: 'Referensi',
            cell: (rfq) => (
                <div className="flex flex-col leading-tight">
                    <span
                        dir="ltr"
                        className={cn(
                            'tabular-nums',
                            !rfq.is_read && 'font-semibold',
                        )}
                    >
                        {rfq.reference_no}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        {rfq.created_at}
                    </span>
                </div>
            ),
        },
        {
            id: 'company_name',
            header: 'Perusahaan',
            sortKey: 'company_name',
            cell: (rfq) => (
                <div className="flex flex-col leading-tight">
                    <span className={cn(!rfq.is_read && 'font-semibold')}>
                        {rfq.company_name}
                    </span>
                    <span className="text-xs text-muted-foreground">
                        {rfq.contact_name ?? rfq.email}
                    </span>
                </div>
            ),
        },
        {
            id: 'country_code',
            header: 'Negara',
            sortKey: 'country_code',
            cell: (rfq) => <span dir="ltr">{rfq.country_code}</span>,
        },
        {
            id: 'monthly_tonnage',
            header: 'Volume',
            hiddenByDefault: true,
            cell: (rfq) => rfq.monthly_tonnage ?? '—',
        },
        {
            id: 'status',
            header: 'Status',
            sortKey: 'status',
            cell: (rfq) => (
                <StatusBadge
                    label={rfq.status_label}
                    tone={rfq.status_color as StatusTone}
                />
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (rfq) => (
                <Button asChild variant="detail" size="sm">
                    <Link href={rfqShow(rfq.id)}>
                        <Eye />
                        Detail
                    </Link>
                </Button>
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="RFQ Inbox"
                description="Permintaan penawaran yang masuk dari halaman publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'RFQ Inbox', href: rfqsIndex() },
                ]}
                actions={
                    <Button asChild variant="export">
                        <a
                            href={
                                exportRfqs({
                                    query: filters as Record<string, string>,
                                }).url
                            }
                        >
                            <Download />
                            Ekspor CSV
                        </a>
                    </Button>
                }
            />

            {unreadCount > 0 && (
                <div className="flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                    <Inbox
                        aria-hidden
                        className="mt-0.5 size-4 shrink-0 text-info"
                    />
                    <p className="text-[13px] text-info">
                        {unreadCount} permintaan belum dibaca. Buyer biasanya
                        mengirim permintaan yang sama ke beberapa pemasok
                        sekaligus — balas lebih dulu.
                    </p>
                </div>
            )}

            <DataTable
                rows={rfqs}
                columns={columns}
                filters={filters}
                rowId={(rfq) => rfq.id}
                reloadOnly={['rfqs', 'unreadCount', 'filters']}
                searchPlaceholder="Cari referensi, perusahaan, atau email…"
                emptyTitle="Belum ada permintaan"
                emptyDescription="Permintaan penawaran dari halaman publik akan muncul di sini."
                filterSlot={
                    <>
                        <Select
                            value={
                                typeof filters.status === 'string' &&
                                filters.status !== ''
                                    ? filters.status
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'status',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-44">
                                <SelectValue placeholder="Semua status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua status
                                </SelectItem>
                                {statuses.map((status) => (
                                    <SelectItem
                                        key={status.value}
                                        value={status.value}
                                    >
                                        {status.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.unread === '1' ? '1' : 'all'}
                            onValueChange={(value) =>
                                applyFilter(
                                    'unread',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-40">
                                <SelectValue placeholder="Semua" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua</SelectItem>
                                <SelectItem value="1">Belum dibaca</SelectItem>
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
                                    rfqsIndex().url,
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
        </>
    );
}
