import { Link, router } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { DeleteButton, EditButton } from '@/components/shared/action-buttons';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import EmptyState from '@/components/shared/empty-state';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import SortableList from '@/components/shared/sortable-list';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { cn, toUrl } from '@/lib/utils';
import { dashboard } from '@/routes/admin';

/**
 * Baris yang siap ditampilkan; tiap modul memetakan resource-nya sendiri ke
 * bentuk ini.
 */
export type SortableContentItem = {
    id: number;
    title: string;
    subtitle?: string | null;
    imageUrl?: string | null;
    /** Teks kecil di sisi akhir baris, misalnya nilai hasil uji. */
    badge?: string | null;
    isActive: boolean;
};

type Href = NonNullable<InertiaLinkProps['href']>;

type Props = {
    items: SortableContentItem[];
    title: string;
    description: string;
    indexHref: Href;
    createHref: Href;
    editHref: (id: number) => Href;
    destroyHref: (id: number) => Href;
    reorderHref: Href;
    createLabel: string;
    emptyTitle: string;
    emptyDescription: string;
    /** Kalimat yang menjelaskan dampak penghapusan di halaman publik. */
    deleteDescription: string;
    /** Kata benda untuk judul dialog, misalnya "tahapan produksi". */
    itemNoun: string;
    /**
     * `contain` untuk logo: wordmark yang lebih lebar dari tinggi akan terpotong
     * kalau dipangkas seperti foto.
     */
    imageFit?: 'cover' | 'contain';
};

/**
 * Halaman index bersama untuk konten perusahaan berurutan: daftar pendek,
 * urutan diatur drag-and-drop, tanpa pagination.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.6
 * @see docs/09-tasks.md bagian 3.3
 */
export default function SortableContentIndex({
    items,
    title,
    description,
    indexHref,
    createHref,
    editHref,
    destroyHref,
    reorderHref,
    createLabel,
    emptyTitle,
    emptyDescription,
    deleteDescription,
    imageFit = 'cover',
    itemNoun,
}: Props) {
    const [rows, setRows] = useState(items);
    const [syncedItems, setSyncedItems] = useState(items);
    const [deleting, setDeleting] = useState<SortableContentItem | null>(null);
    const [processing, setProcessing] = useState(false);

    // Daftar lokal disinkronkan ulang saat server mengirim data baru, misalnya
    // setelah simpan atau hapus. Penyesuaian dilakukan saat render — bukan di
    // dalam efek — supaya tidak ada render perantara dengan data usang.
    if (syncedItems !== items) {
        setSyncedItems(items);
        setRows(items);
    }

    const handleReorder = (next: SortableContentItem[]) => {
        const previous = rows;

        setRows(next);

        router.post(
            toUrl(reorderHref),
            { ids: next.map((item) => item.id) },
            {
                preserveScroll: true,
                // Urutan lama dikembalikan supaya layar tidak menampilkan
                // susunan yang gagal tersimpan.
                onError: () => setRows(previous),
            },
        );
    };

    return (
        <>
            <PageHeader
                title={title}
                description={description}
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title, href: indexHref },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={createHref}>
                            <Plus />
                            {createLabel}
                        </Link>
                    </Button>
                }
            />

            <Panel
                title="Urutan Tampil"
                description="Geser baris untuk mengubah urutannya di halaman publik. Perubahan langsung tersimpan."
            >
                {rows.length === 0 ? (
                    <EmptyState
                        title={emptyTitle}
                        description={emptyDescription}
                        action={
                            <Button asChild variant="create">
                                <Link href={createHref}>
                                    <Plus />
                                    {createLabel}
                                </Link>
                            </Button>
                        }
                    />
                ) : (
                    <SortableList
                        items={rows}
                        itemId={(item) => item.id}
                        onReorder={handleReorder}
                    >
                        {(item) => (
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div className="flex min-w-0 items-center gap-3">
                                    {item.imageUrl && (
                                        <img
                                            src={item.imageUrl}
                                            alt=""
                                            className={cn(
                                                'size-9 shrink-0 rounded-[var(--radius)] border border-border',
                                                imageFit === 'contain'
                                                    ? 'bg-card object-contain p-1'
                                                    : 'object-cover',
                                            )}
                                        />
                                    )}
                                    <div className="flex min-w-0 flex-col leading-tight">
                                        <span className="truncate font-medium">
                                            {item.title}
                                        </span>
                                        {item.subtitle && (
                                            <span className="truncate text-xs text-muted-foreground">
                                                {item.subtitle}
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="flex items-center gap-2">
                                    {item.badge && (
                                        <span
                                            dir="ltr"
                                            className="text-xs text-muted-foreground tabular-nums"
                                        >
                                            {item.badge}
                                        </span>
                                    )}
                                    <StatusBadge
                                        label={
                                            item.isActive ? 'Aktif' : 'Nonaktif'
                                        }
                                        tone={
                                            item.isActive ? 'success' : 'muted'
                                        }
                                    />
                                    <EditButton href={editHref(item.id)} />
                                    <DeleteButton
                                        onClick={() => setDeleting(item)}
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
                title={`Hapus ${itemNoun} "${deleting?.title ?? ''}"?`}
                description={deleteDescription}
                confirmLabel="Hapus"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(toUrl(destroyHref(deleting.id)), {
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
