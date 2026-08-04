import { router } from '@inertiajs/react';
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
    destroy as destroyCategory,
    index as categoriesIndex,
} from '@/routes/admin/product-categories';
import type { ProductCategory } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';
import CategoryFormDialog from './category-form-dialog';

type Props = {
    categories: Paginated<ProductCategory>;
    filters: TableFilters;
};

export default function ProductCategoryIndex({ categories, filters }: Props) {
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState<ProductCategory | null>(null);
    const [deleting, setDeleting] = useState<ProductCategory | null>(null);
    const [processing, setProcessing] = useState(false);

    const openCreate = () => {
        setEditing(null);
        setFormOpen(true);
    };

    const openEdit = (category: ProductCategory) => {
        setEditing(category);
        setFormOpen(true);
    };

    const columns: DataTableColumn<ProductCategory>[] = [
        {
            id: 'name',
            header: 'Nama',
            sortKey: 'sort_order',
            cell: (category) => (
                <div className="flex flex-col leading-tight">
                    <span className="font-medium">
                        {category.name?.id ?? '—'}
                    </span>
                    <span dir="ltr" className="text-xs text-muted-foreground">
                        /{category.slug?.id ?? ''}
                    </span>
                </div>
            ),
        },
        {
            id: 'icon',
            header: 'Ikon',
            hiddenByDefault: true,
            cell: (category) => (
                <span dir="ltr" className="text-muted-foreground">
                    {category.icon ?? '—'}
                </span>
            ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (category) => (
                <StatusBadge
                    label={category.is_active ? 'Aktif' : 'Nonaktif'}
                    tone={category.is_active ? 'success' : 'muted'}
                />
            ),
        },
        {
            id: 'created_at',
            header: 'Dibuat',
            sortKey: 'created_at',
            hiddenByDefault: true,
            cell: (category) => (
                <span className="text-muted-foreground tabular-nums">
                    {category.created_at ?? '—'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (category) => (
                <DataTableRowActions
                    onEdit={() => openEdit(category)}
                    onDelete={() => setDeleting(category)}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Kategori Produk"
                description="Kelompokkan produk briket agar mudah ditelusuri buyer di website publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Kategori Produk', href: categoriesIndex() },
                ]}
                actions={
                    <Button variant="create" onClick={openCreate}>
                        <Plus />
                        Tambah Kategori
                    </Button>
                }
            />

            <DataTable
                rows={categories}
                columns={columns}
                filters={filters}
                rowId={(category) => category.id}
                reloadOnly={['categories', 'filters']}
                searchPlaceholder="Cari nama kategori…"
                emptyTitle="Belum ada kategori produk"
                emptyDescription="Tambahkan kategori pertama agar produk bisa dikelompokkan."
                emptyAction={
                    <Button variant="create" onClick={openCreate}>
                        <Plus />
                        Tambah Kategori
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
                                categoriesIndex().url,
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
                            <SelectItem value="all">Semua status</SelectItem>
                            <SelectItem value="1">Aktif</SelectItem>
                            <SelectItem value="0">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                }
            />

            <CategoryFormDialog
                open={formOpen}
                onOpenChange={setFormOpen}
                category={editing}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus kategori "${deleting?.name?.id ?? ''}"?`}
                description="Kategori dipindahkan ke arsip dan tidak lagi tampil di website publik. Produk yang memakainya tidak ikut terhapus."
                confirmLabel="Hapus Kategori"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyCategory(deleting.id).url, {
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
