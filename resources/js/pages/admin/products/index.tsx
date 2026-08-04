import { Link, router } from '@inertiajs/react';
import {
    Archive,
    Copy,
    ImageOff,
    Plus,
    Power,
    PowerOff,
    TriangleAlert,
} from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import { DataTableRowActions } from '@/components/data-table/data-table-row-actions';
import BulkConfirmDialog from '@/components/shared/bulk-confirm-dialog';
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
    bulk as bulkProducts,
    destroy as destroyProduct,
    duplicate as duplicateProduct,
    create as productCreate,
    edit as productEdit,
    index as productsIndex,
    show as productShow,
} from '@/routes/admin/products';
import type { Product, SelectOption } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const LOCALE = 'id';

type BulkAction = 'activate' | 'deactivate' | 'archive';

const bulkLabels: Record<BulkAction, string> = {
    activate: 'Aktifkan',
    deactivate: 'Nonaktifkan',
    archive: 'Arsipkan',
};

const bulkConsequences: Record<BulkAction, string> = {
    activate: 'Produk akan tampil di website publik.',
    deactivate: 'Produk disembunyikan dari website publik, datanya tetap utuh.',
    archive: 'Produk dipindahkan ke arsip dan bisa dipulihkan kembali.',
};

type Props = {
    products: Paginated<Product>;
    categories: SelectOption[];
    filters: TableFilters;
};

export default function ProductIndex({ products, categories, filters }: Props) {
    const [deleting, setDeleting] = useState<Product | null>(null);
    const [processing, setProcessing] = useState(false);
    const [bulk, setBulk] = useState<{
        action: BulkAction;
        ids: Array<string | number>;
    } | null>(null);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            productsIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<Product>[] = [
        {
            id: 'cover',
            header: 'Gambar',
            cell: (product) =>
                product.cover_url ? (
                    <img
                        src={product.cover_url}
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
            header: 'Produk',
            sortKey: 'sort_order',
            cell: (product) => (
                <div className="flex flex-col leading-tight">
                    <span className="flex items-center gap-1.5 font-medium">
                        <Link
                            href={productShow(product.id)}
                            className="hover:underline"
                        >
                            {product.name?.[LOCALE] ?? '—'}
                        </Link>
                        {!product.is_calculable && (
                            <span
                                title="Data master carton belum lengkap — produk tidak muncul di Container Calculator"
                                className="inline-flex text-warning"
                            >
                                <TriangleAlert
                                    aria-hidden
                                    className="size-3.5"
                                />
                                <span className="sr-only">
                                    Data master carton belum lengkap
                                </span>
                            </span>
                        )}
                    </span>
                    <span dir="ltr" className="text-xs text-muted-foreground">
                        {product.code}
                    </span>
                </div>
            ),
        },
        {
            id: 'category',
            header: 'Kategori',
            cell: (product) => (
                <span className="text-muted-foreground">
                    {product.category?.name?.[LOCALE] ?? 'Tanpa kategori'}
                </span>
            ),
        },
        {
            id: 'carton',
            header: 'Master carton',
            hiddenByDefault: true,
            cell: (product) =>
                product.is_calculable ? (
                    <span dir="ltr" className="tabular-nums">
                        {product.master_carton_weight_kg} kg ·{' '}
                        {product.master_carton_cbm} CBM
                    </span>
                ) : (
                    <span className="text-warning">Belum lengkap</span>
                ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (product) => (
                <div className="flex flex-wrap items-center gap-1.5">
                    <StatusBadge
                        label={product.is_active ? 'Aktif' : 'Nonaktif'}
                        tone={product.is_active ? 'success' : 'muted'}
                    />
                    {product.is_featured && (
                        <StatusBadge label="Unggulan" tone="primary" />
                    )}
                </div>
            ),
        },
        {
            id: 'created_at',
            header: 'Dibuat',
            sortKey: 'created_at',
            hiddenByDefault: true,
            cell: (product) => (
                <span className="text-muted-foreground tabular-nums">
                    {product.created_at ?? '—'}
                </span>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (product) => (
                <DataTableRowActions
                    detailHref={productShow(product.id)}
                    editHref={productEdit(product.id)}
                    onDelete={() => setDeleting(product)}
                    extraActions={[
                        {
                            label: 'Duplikat',
                            icon: <Copy />,
                            onSelect: () =>
                                router.post(
                                    duplicateProduct(product.id).url,
                                    {},
                                    { preserveScroll: true },
                                ),
                        },
                    ]}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Produk"
                description="Kelola daftar produk briket beserta data pengiriman dan gambarnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Produk', href: productsIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={productCreate()}>
                            <Plus />
                            Tambah Produk
                        </Link>
                    </Button>
                }
            />

            <DataTable
                rows={products}
                columns={columns}
                filters={filters}
                rowId={(product) => product.id}
                reloadOnly={['products', 'filters']}
                searchPlaceholder="Cari nama atau kode produk…"
                emptyTitle="Belum ada produk"
                emptyDescription="Tambahkan produk pertama beserta data master carton-nya."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={productCreate()}>
                            <Plus />
                            Tambah Produk
                        </Link>
                    </Button>
                }
                filterSlot={
                    <>
                        <Select
                            value={
                                filters.product_category_id
                                    ? String(filters.product_category_id)
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'product_category_id',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-48">
                                <SelectValue placeholder="Semua kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua kategori
                                </SelectItem>
                                {categories.map((category) => (
                                    <SelectItem
                                        key={category.value}
                                        value={category.value}
                                    >
                                        {category.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

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
                                filters.missing_carton === '1'
                                    ? 'missing'
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'missing_carton',
                                    value === 'missing' ? '1' : undefined,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-52">
                                <SelectValue placeholder="Semua data carton" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua data carton
                                </SelectItem>
                                <SelectItem value="missing">
                                    Master carton belum lengkap
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </>
                }
                bulkActions={(selected) => (
                    <>
                        <Button
                            variant="toggle"
                            size="sm"
                            onClick={() =>
                                setBulk({ action: 'activate', ids: selected })
                            }
                        >
                            <Power />
                            Aktifkan
                        </Button>
                        <Button
                            variant="cancel"
                            size="sm"
                            onClick={() =>
                                setBulk({ action: 'deactivate', ids: selected })
                            }
                        >
                            <PowerOff />
                            Nonaktifkan
                        </Button>
                        <Button
                            variant="archive"
                            size="sm"
                            onClick={() =>
                                setBulk({ action: 'archive', ids: selected })
                            }
                        >
                            <Archive />
                            Arsipkan
                        </Button>
                    </>
                )}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus produk "${deleting?.name?.[LOCALE] ?? ''}"?`}
                description="Produk dipindahkan ke arsip beserta gambarnya, dan masih bisa dipulihkan."
                confirmLabel="Hapus Produk"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyProduct(deleting.id).url, {
                        preserveScroll: true,
                        onFinish: () => {
                            setProcessing(false);
                            setDeleting(null);
                        },
                    });
                }}
            />

            <BulkConfirmDialog
                open={bulk !== null}
                onOpenChange={(open) => !open && setBulk(null)}
                tone={bulk?.action === 'archive' ? 'danger' : 'warning'}
                entity="produk"
                names={
                    bulk
                        ? products.data
                              .filter((product) =>
                                  bulk.ids.includes(product.id),
                              )
                              .map((product) => product.name?.[LOCALE] ?? '')
                        : []
                }
                consequence={bulk ? bulkConsequences[bulk.action] : ''}
                confirmLabel={bulk ? bulkLabels[bulk.action] : ''}
                loading={processing}
                onConfirm={() => {
                    if (!bulk) {
                        return;
                    }

                    setProcessing(true);
                    router.post(
                        bulkProducts().url,
                        { action: bulk.action, ids: bulk.ids },
                        {
                            preserveScroll: true,
                            onFinish: () => {
                                setProcessing(false);
                                setBulk(null);
                            },
                        },
                    );
                }}
            />
        </>
    );
}
