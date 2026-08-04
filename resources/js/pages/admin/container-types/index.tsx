import { Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
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
    create as containerTypeCreate,
    destroy as destroyContainerType,
    edit as containerTypeEdit,
    index as containerTypesIndex,
} from '@/routes/admin/container-types';
import type { ContainerType } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const LOCALE = 'id';

const decimal = (value: number | null, fractionDigits = 2) =>
    value === null
        ? '—'
        : value.toLocaleString('id-ID', {
              minimumFractionDigits: 0,
              maximumFractionDigits: fractionDigits,
          });

type Props = {
    containerTypes: Paginated<ContainerType>;
    filters: TableFilters;
};

export default function ContainerTypeIndex({ containerTypes, filters }: Props) {
    const [deleting, setDeleting] = useState<ContainerType | null>(null);
    const [processing, setProcessing] = useState(false);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            containerTypesIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<ContainerType>[] = [
        {
            id: 'name',
            header: 'Kontainer',
            sortKey: 'sort_order',
            cell: (containerType) => (
                <div className="flex flex-col leading-tight">
                    <span className="font-medium">
                        {containerType.name?.[LOCALE] ?? '—'}
                    </span>
                    <span dir="ltr" className="text-xs text-muted-foreground">
                        {containerType.code}
                    </span>
                </div>
            ),
        },
        {
            id: 'max_payload_kg',
            header: 'Kapasitas berat',
            sortKey: 'max_payload_kg',
            cell: (containerType) => (
                <span dir="ltr" className="tabular-nums">
                    {decimal(containerType.max_payload_kg)} kg
                </span>
            ),
        },
        {
            id: 'max_cbm',
            header: 'Kapasitas volume',
            sortKey: 'max_cbm',
            cell: (containerType) => (
                <span dir="ltr" className="tabular-nums">
                    {decimal(containerType.max_cbm)} CBM
                </span>
            ),
        },
        {
            id: 'dimensions',
            header: 'Dimensi internal',
            hiddenByDefault: true,
            cell: (containerType) =>
                containerType.internal_volume_cbm === null ? (
                    <span className="text-muted-foreground">Belum diisi</span>
                ) : (
                    <span dir="ltr" className="tabular-nums">
                        {decimal(containerType.internal_length_m)} ×{' '}
                        {decimal(containerType.internal_width_m)} ×{' '}
                        {decimal(containerType.internal_height_m)} m
                    </span>
                ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (containerType) => (
                <StatusBadge
                    label={containerType.is_active ? 'Aktif' : 'Nonaktif'}
                    tone={containerType.is_active ? 'success' : 'muted'}
                />
            ),
        },
        {
            id: 'created_at',
            header: 'Dibuat',
            sortKey: 'created_at',
            hiddenByDefault: true,
            cell: (containerType) => (
                <span className="text-muted-foreground tabular-nums">
                    {containerType.created_at ?? '—'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (containerType) => (
                <DataTableRowActions
                    editHref={containerTypeEdit(containerType.id)}
                    onDelete={() => setDeleting(containerType)}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Tipe Kontainer"
                description="Kelola kapasitas muat tiap kontainer yang dipakai Container Calculator."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tipe Kontainer', href: containerTypesIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={containerTypeCreate()}>
                            <Plus />
                            Tambah Tipe Kontainer
                        </Link>
                    </Button>
                }
            />

            <DataTable
                rows={containerTypes}
                columns={columns}
                filters={filters}
                rowId={(containerType) => containerType.id}
                reloadOnly={['containerTypes', 'filters']}
                searchPlaceholder="Cari nama atau kode kontainer…"
                emptyTitle="Belum ada tipe kontainer"
                emptyDescription="Tambahkan 20FT Dry Van dan 40FT High Cube agar Container Calculator bisa dipakai."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={containerTypeCreate()}>
                            <Plus />
                            Tambah Tipe Kontainer
                        </Link>
                    </Button>
                }
                filterSlot={
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
                            <SelectItem value="all">Semua status</SelectItem>
                            <SelectItem value="1">Aktif</SelectItem>
                            <SelectItem value="0">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                }
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus tipe kontainer "${deleting?.name?.[LOCALE] ?? ''}"?`}
                description="Kontainer dipindahkan ke arsip dan hilang dari Container Calculator. RFQ lama yang memakainya tetap tersimpan."
                confirmLabel="Hapus Tipe Kontainer"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyContainerType(deleting.id).url, {
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
