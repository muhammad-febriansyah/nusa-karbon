import { router } from '@inertiajs/react';
import { ArrowUpDown, ImageOff, Plus, Save, X } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import { DataTableRowActions } from '@/components/data-table/data-table-row-actions';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import SortableList from '@/components/shared/sortable-list';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { dashboard } from '@/routes/admin';
import {
    destroy as destroyPackagingOption,
    index as packagingOptionsIndex,
    reorder as reorderPackagingOptions,
} from '@/routes/admin/packaging-options';
import type { PackagingOption } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';
import PackagingFormDialog from './packaging-form-dialog';

const LOCALE = 'id';

const weight = (value: number | null) =>
    value === null
        ? 'Custom'
        : `${value.toLocaleString('id-ID', { maximumFractionDigits: 3 })} kg`;

type Props = {
    packagingOptions: Paginated<PackagingOption>;
    /** Hanya dimuat saat mode atur urutan aktif. */
    orderedOptions?: PackagingOption[];
    filters: TableFilters;
};

export default function PackagingOptionIndex({
    packagingOptions,
    orderedOptions,
    filters,
}: Props) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<PackagingOption | null>(null);
    const [deleting, setDeleting] = useState<PackagingOption | null>(null);
    const [processing, setProcessing] = useState(false);

    const [reordering, setReordering] = useState(false);
    // Null berarti belum digeser: urutannya masih persis seperti dari server.
    const [draft, setDraft] = useState<PackagingOption[] | null>(null);

    const rows = draft ?? orderedOptions ?? [];

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (option: PackagingOption) => {
        setEditing(option);
        setFormOpen(true);
    };

    const startReorder = () => {
        setReordering(true);
        setDraft(null);

        // `orderedOptions` sengaja opsional di server: daftar penuh tanpa
        // pagination baru diambil saat mode ini dibuka.
        router.reload({ only: ['orderedOptions'] });
    };

    const cancelReorder = () => {
        setReordering(false);
        setDraft(null);
    };

    const saveOrder = () => {
        setProcessing(true);
        router.post(
            reorderPackagingOptions().url,
            { ids: rows.map((option) => option.id) },
            {
                preserveScroll: true,
                onSuccess: () => cancelReorder(),
                onFinish: () => setProcessing(false),
            },
        );
    };

    const columns: DataTableColumn<PackagingOption>[] = [
        {
            id: 'image',
            header: 'Gambar',
            cell: (option) =>
                option.image_url ? (
                    <img
                        src={option.image_url}
                        alt=""
                        className="size-10 rounded-[var(--radius)] object-cover"
                    />
                ) : (
                    <ImageOff
                        aria-hidden
                        className="size-10 rounded-[var(--radius)] bg-muted p-2.5 text-muted-foreground"
                    />
                ),
        },
        {
            id: 'name',
            header: 'Kemasan',
            sortKey: 'sort_order',
            cell: (option) => (
                <div className="flex flex-col leading-tight">
                    <span className="font-medium">
                        {option.name?.[LOCALE] ?? '—'}
                    </span>
                    <span className="line-clamp-1 text-xs text-muted-foreground">
                        {option.description?.[LOCALE] || 'Tanpa deskripsi'}
                    </span>
                </div>
            ),
        },
        {
            id: 'net_weight_kg',
            header: 'Berat bersih',
            sortKey: 'net_weight_kg',
            cell: (option) => (
                <span dir="ltr" className="tabular-nums">
                    {weight(option.net_weight_kg)}
                </span>
            ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (option) => (
                <StatusBadge
                    label={option.is_active ? 'Aktif' : 'Nonaktif'}
                    tone={option.is_active ? 'success' : 'muted'}
                />
            ),
        },
        {
            id: 'created_at',
            header: 'Dibuat',
            sortKey: 'created_at',
            hiddenByDefault: true,
            cell: (option) => (
                <span className="text-muted-foreground tabular-nums">
                    {option.created_at ?? '—'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (option) => (
                <DataTableRowActions
                    onEdit={() => openEdit(option)}
                    onDelete={() => setDeleting(option)}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Opsi Kemasan"
                description="Kelola pilihan kemasan yang ditawarkan ke buyer beserta gambar dan berat bersihnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Opsi Kemasan', href: packagingOptionsIndex() },
                ]}
                actions={
                    reordering ? (
                        <>
                            <Button
                                variant="cancel"
                                onClick={cancelReorder}
                                disabled={processing}
                            >
                                <X />
                                Batal
                            </Button>
                            <Button
                                variant="save"
                                onClick={saveOrder}
                                disabled={processing || rows.length === 0}
                            >
                                {processing ? <Spinner /> : <Save />}
                                {processing ? 'Menyimpan...' : 'Simpan Urutan'}
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button variant="filter" onClick={startReorder}>
                                <ArrowUpDown />
                                Atur Urutan
                            </Button>
                            <Button variant="create" onClick={openCreate}>
                                <Plus />
                                Tambah Kemasan
                            </Button>
                        </>
                    )
                }
            />

            {reordering ? (
                <Panel
                    title="Atur Urutan Kemasan"
                    description="Geser baris untuk mengubah urutan tampil di halaman produk dan form RFQ."
                >
                    {rows.length === 0 ? (
                        <p className="py-6 text-center text-sm text-muted-foreground">
                            Memuat daftar kemasan…
                        </p>
                    ) : (
                        <SortableList
                            items={rows}
                            itemId={(option) => option.id}
                            onReorder={setDraft}
                        >
                            {(option, index) => (
                                <div className="flex items-center gap-3">
                                    <span className="w-6 text-xs text-muted-foreground tabular-nums">
                                        {index + 1}.
                                    </span>
                                    {option.image_url ? (
                                        <img
                                            src={option.image_url}
                                            alt=""
                                            className="size-8 rounded-[var(--radius)] object-cover"
                                        />
                                    ) : (
                                        <ImageOff
                                            aria-hidden
                                            className="size-8 rounded-[var(--radius)] bg-muted p-2 text-muted-foreground"
                                        />
                                    )}
                                    <span className="min-w-0 flex-1 truncate font-medium">
                                        {option.name?.[LOCALE] ?? '—'}
                                    </span>
                                    <span
                                        dir="ltr"
                                        className="text-xs text-muted-foreground tabular-nums"
                                    >
                                        {weight(option.net_weight_kg)}
                                    </span>
                                    {!option.is_active && (
                                        <StatusBadge
                                            label="Nonaktif"
                                            tone="muted"
                                        />
                                    )}
                                </div>
                            )}
                        </SortableList>
                    )}
                </Panel>
            ) : (
                <DataTable
                    rows={packagingOptions}
                    columns={columns}
                    filters={filters}
                    rowId={(option) => option.id}
                    reloadOnly={['packagingOptions', 'filters']}
                    searchPlaceholder="Cari nama kemasan…"
                    emptyTitle="Belum ada opsi kemasan"
                    emptyDescription="Tambahkan kemasan pertama agar bisa dipilih di form produk."
                    emptyAction={
                        <Button variant="create" onClick={openCreate}>
                            <Plus />
                            Tambah Kemasan
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
                                router.get(
                                    packagingOptionsIndex().url,
                                    {
                                        ...filters,
                                        is_active:
                                            value === 'all' ? undefined : value,
                                        page: 1,
                                    },
                                    { preserveState: true, replace: true },
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
                    }
                />
            )}

            <PackagingFormDialog
                open={formOpen}
                onOpenChange={setFormOpen}
                option={editing}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus kemasan "${deleting?.name?.[LOCALE] ?? ''}"?`}
                description="Kemasan dipindahkan ke arsip dan hilang dari form produk. Produk yang memakainya tidak ikut terhapus."
                confirmLabel="Hapus Kemasan"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyPackagingOption(deleting.id).url, {
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
