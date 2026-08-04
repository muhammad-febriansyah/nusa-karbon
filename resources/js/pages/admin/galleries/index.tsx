import { Link, router } from '@inertiajs/react';
import { Images, Plus } from 'lucide-react';
import { useState } from 'react';
import { DeleteButton, EditButton } from '@/components/shared/action-buttons';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import EmptyState from '@/components/shared/empty-state';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import SortableList from '@/components/shared/sortable-list';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import {
    create as galleryCreate,
    destroy as destroyGallery,
    edit as galleryEdit,
    index as galleriesIndex,
    reorder as reorderGalleries,
} from '@/routes/admin/galleries';
import { edit as galleryItemsEdit } from '@/routes/admin/galleries/items';
import type { Gallery } from '@/types/models';

const LOCALE = 'id';

type Props = {
    items: Gallery[];
};

export default function GalleryIndex({ items }: Props) {
    const [rows, setRows] = useState(items);
    const [syncedItems, setSyncedItems] = useState(items);
    const [deleting, setDeleting] = useState<Gallery | null>(null);
    const [processing, setProcessing] = useState(false);

    if (syncedItems !== items) {
        setSyncedItems(items);
        setRows(items);
    }

    const handleReorder = (next: Gallery[]) => {
        const previous = rows;

        setRows(next);

        router.post(
            reorderGalleries().url,
            { ids: next.map((gallery) => gallery.id) },
            {
                preserveScroll: true,
                onError: () => setRows(previous),
            },
        );
    };

    return (
        <>
            <PageHeader
                title="Galeri"
                description="Album foto dan video produksi, QC, dan proses stuffing kontainer."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Galeri', href: galleriesIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={galleryCreate()}>
                            <Plus />
                            Tambah Album
                        </Link>
                    </Button>
                }
            />

            <Panel
                title="Urutan Album"
                description="Geser baris untuk mengubah urutan album di halaman publik. Perubahan langsung tersimpan."
            >
                {rows.length === 0 ? (
                    <EmptyState
                        title="Belum ada album"
                        description="Buat album lalu unggah foto dan video prosesnya."
                        action={
                            <Button asChild variant="create">
                                <Link href={galleryCreate()}>
                                    <Plus />
                                    Tambah Album
                                </Link>
                            </Button>
                        }
                    />
                ) : (
                    <SortableList
                        items={rows}
                        itemId={(gallery) => gallery.id}
                        onReorder={handleReorder}
                    >
                        {(gallery) => (
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex min-w-0 items-center gap-3">
                                    {gallery.cover_url ? (
                                        <img
                                            src={gallery.cover_url}
                                            alt=""
                                            className="size-10 shrink-0 rounded-[var(--radius)] border border-border object-cover"
                                        />
                                    ) : (
                                        <span
                                            aria-hidden
                                            className="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius)] border border-dashed border-border text-muted-foreground"
                                        >
                                            <Images className="size-4" />
                                        </span>
                                    )}
                                    <div className="flex min-w-0 flex-col leading-tight">
                                        <span className="truncate font-medium">
                                            {gallery.title?.[LOCALE] ?? '—'}
                                        </span>
                                        <span
                                            dir="ltr"
                                            className="truncate text-xs text-muted-foreground"
                                        >
                                            {gallery.slug?.[LOCALE] ?? ''}
                                        </span>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    <span className="text-xs text-muted-foreground tabular-nums">
                                        {gallery.items_count ?? 0} item
                                    </span>
                                    <StatusBadge
                                        label={
                                            gallery.is_active
                                                ? 'Aktif'
                                                : 'Nonaktif'
                                        }
                                        tone={
                                            gallery.is_active
                                                ? 'success'
                                                : 'muted'
                                        }
                                    />
                                    <Button asChild variant="upload" size="sm">
                                        <Link
                                            href={galleryItemsEdit(gallery.id)}
                                        >
                                            <Images />
                                            Kelola Isi
                                        </Link>
                                    </Button>
                                    <EditButton
                                        href={galleryEdit(gallery.id)}
                                    />
                                    <DeleteButton
                                        onClick={() => setDeleting(gallery)}
                                    />
                                </div>
                            </div>
                        )}
                    </SortableList>
                )}
            </Panel>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus album "${deleting?.title?.[LOCALE] ?? ''}"?`}
                description="Album beserta seluruh foto dan videonya hilang dari halaman publik. Datanya dipindahkan ke arsip, bukan dihapus permanen."
                confirmLabel="Hapus Album"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyGallery(deleting.id).url, {
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
