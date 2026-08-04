import { Link, router } from '@inertiajs/react';
import { Plus, ShieldCheck } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import { DataTableRowActions } from '@/components/data-table/data-table-row-actions';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import PageHeader from '@/components/shared/page-header';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes/admin';
import {
    create as representativeCreate,
    destroy as destroyRepresentative,
    edit as representativeEdit,
    index as representativesIndex,
} from '@/routes/admin/representatives';
import type { Representative } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const LOCALE = 'id';

const REGIONS = ['MENA', 'Europe', 'Asia', 'Americas', 'Africa'];

const initials = (name: string) =>
    name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');

type Props = {
    representatives: Paginated<Representative>;
    filters: TableFilters;
};

export default function RepresentativeIndex({
    representatives,
    filters,
}: Props) {
    const [deleting, setDeleting] = useState<Representative | null>(null);
    const [processing, setProcessing] = useState(false);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            representativesIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<Representative>[] = [
        {
            id: 'name',
            header: 'Perwakilan',
            sortKey: 'name',
            cell: (representative) => (
                <div className="flex items-center gap-3">
                    {representative.photo_url ? (
                        <img
                            src={representative.photo_url}
                            alt=""
                            className="size-9 shrink-0 rounded-full object-cover"
                        />
                    ) : (
                        <span
                            aria-hidden
                            className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/12 text-xs font-semibold text-primary"
                        >
                            {initials(representative.name)}
                        </span>
                    )}
                    <div className="flex flex-col leading-tight">
                        <span className="font-medium">
                            {representative.name}
                        </span>
                        <span className="text-xs text-muted-foreground">
                            {representative.position?.[LOCALE] ?? '—'}
                        </span>
                    </div>
                </div>
            ),
        },
        {
            id: 'email',
            header: 'Email resmi',
            cell: (representative) => (
                <span dir="ltr" className="text-muted-foreground">
                    {representative.email}
                </span>
            ),
        },
        {
            id: 'region',
            header: 'Wilayah',
            sortKey: 'region',
            cell: (representative) => representative.region ?? '—',
        },
        {
            id: 'whatsapp',
            header: 'WhatsApp',
            hiddenByDefault: true,
            cell: (representative) => (
                <span dir="ltr" className="tabular-nums">
                    {representative.whatsapp ?? '—'}
                </span>
            ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (representative) => (
                <StatusBadge
                    label={representative.is_active ? 'Aktif' : 'Nonaktif'}
                    tone={representative.is_active ? 'success' : 'muted'}
                />
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (representative) => (
                <DataTableRowActions
                    editHref={representativeEdit(representative.id)}
                    onDelete={() => setDeleting(representative)}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Perwakilan Resmi"
                description="Daftar orang yang berhak mewakili perusahaan; dipakai buyer untuk memverifikasi kontak yang menghubunginya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Perwakilan Resmi', href: representativesIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={representativeCreate()}>
                            <Plus />
                            Tambah Perwakilan
                        </Link>
                    </Button>
                }
            />

            <div className="flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                <ShieldCheck
                    aria-hidden
                    className="mt-0.5 size-4 shrink-0 text-info"
                />
                <p className="text-[13px] text-info">
                    Setiap perubahan di halaman ini tercatat lengkap beserta
                    nilai lama dan barunya. Data ini dipakai buyer untuk
                    memastikan mereka tidak berurusan dengan penipu.
                </p>
            </div>

            <DataTable
                rows={representatives}
                columns={columns}
                filters={filters}
                rowId={(representative) => representative.id}
                reloadOnly={['representatives', 'filters']}
                searchPlaceholder="Cari nama, email, atau wilayah…"
                emptyTitle="Belum ada perwakilan resmi"
                emptyDescription="Tambahkan perwakilan agar buyer bisa memverifikasi siapa saja yang berhak menghubungi mereka."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={representativeCreate()}>
                            <Plus />
                            Tambah Perwakilan
                        </Link>
                    </Button>
                }
                filterSlot={
                    <>
                        <Select
                            value={
                                filters.is_active === undefined ||
                                filters.is_active === ''
                                    ? 'all'
                                    : String(filters.is_active)
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'is_active',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-40">
                                <SelectValue placeholder="Semua status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua status
                                </SelectItem>
                                <SelectItem value="1">Aktif</SelectItem>
                                <SelectItem value="0">Nonaktif</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select
                            value={
                                typeof filters.region === 'string' &&
                                filters.region !== ''
                                    ? filters.region
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'region',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-40">
                                <SelectValue placeholder="Semua wilayah" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua wilayah
                                </SelectItem>
                                {REGIONS.map((region) => (
                                    <SelectItem key={region} value={region}>
                                        {region}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </>
                }
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus perwakilan "${deleting?.name ?? ''}"?`}
                description="Perwakilan hilang dari halaman verifikasi publik. Catatannya tetap tersimpan di arsip dan activity log agar bisa ditelusuri bila ada laporan penipuan."
                confirmLabel="Hapus Perwakilan"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyRepresentative(deleting.id).url, {
                        preserveScroll: true,
                        onFinish: () => {
                            setProcessing(false);
                            setDeleting(null);
                        },
                    });
                }}
            />
        </>
    );
}
