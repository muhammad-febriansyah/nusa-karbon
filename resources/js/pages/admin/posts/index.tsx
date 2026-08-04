import { Link, router } from '@inertiajs/react';
import { Eye, Plus, Send, Tags } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import { DataTableRowActions } from '@/components/data-table/data-table-row-actions';
import DateRangePicker from '@/components/form/date-range-picker';
import ConfirmDialog from '@/components/shared/confirm-dialog';
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
import { dashboard } from '@/routes/admin';
import { index as postCategoriesIndex } from '@/routes/admin/post-categories';
import {
    create as postCreate,
    destroy as destroyPost,
    edit as postEdit,
    index as postsIndex,
    preview as postPreview,
    publish as publishPost,
} from '@/routes/admin/posts';
import type { Post, PostCategory, SelectOption } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const LOCALE = 'id';

type Props = {
    posts: Paginated<Post>;
    categories: PostCategory[];
    statuses: SelectOption[];
    filters: TableFilters;
};

export default function PostIndex({
    posts,
    categories,
    statuses,
    filters,
}: Props) {
    const [deleting, setDeleting] = useState<Post | null>(null);
    const [publishing, setPublishing] = useState<Post | null>(null);
    const [processing, setProcessing] = useState(false);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            postsIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<Post>[] = [
        {
            id: 'title',
            header: 'Artikel',
            cell: (post) => (
                <div className="flex items-center gap-3">
                    {post.cover_url && (
                        <img
                            src={post.cover_url}
                            alt=""
                            className="h-9 w-14 shrink-0 rounded-[var(--radius)] border border-border object-cover"
                        />
                    )}
                    <div className="flex min-w-0 flex-col leading-tight">
                        <span className="truncate font-medium">
                            {post.title?.[LOCALE] ?? '—'}
                        </span>
                        <span className="truncate text-xs text-muted-foreground">
                            {post.category_name?.[LOCALE] ?? 'Tanpa kategori'}
                        </span>
                    </div>
                </div>
            ),
        },
        {
            id: 'status',
            header: 'Status',
            sortKey: 'status',
            cell: (post) => (
                <StatusBadge
                    label={post.status_label}
                    tone={post.status_color as StatusTone}
                />
            ),
        },
        {
            id: 'published_at',
            header: 'Tayang',
            sortKey: 'published_at',
            cell: (post) => (
                <span className="tabular-nums">
                    {post.published_at_display ?? '—'}
                </span>
            ),
        },
        {
            id: 'reading_minutes',
            header: 'Baca',
            hiddenByDefault: true,
            cell: (post) =>
                post.reading_minutes === null
                    ? '—'
                    : `${post.reading_minutes} menit`,
        },
        {
            id: 'view_count',
            header: 'Dilihat',
            sortKey: 'view_count',
            hiddenByDefault: true,
            cell: (post) => (
                <span className="tabular-nums">{post.view_count}</span>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (post) => (
                <div className="flex items-center justify-end gap-2">
                    <Button asChild variant="detail" size="sm">
                        <Link href={postPreview(post.id)}>
                            <Eye />
                            Pratinjau
                        </Link>
                    </Button>
                    {post.status !== 'published' && (
                        <Button
                            type="button"
                            variant="publish"
                            size="sm"
                            onClick={() => setPublishing(post)}
                        >
                            <Send />
                            Terbitkan
                        </Button>
                    )}
                    <DataTableRowActions
                        editHref={postEdit(post.id)}
                        onDelete={() => setDeleting(post)}
                    />
                </div>
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Artikel"
                description="Tulisan blog beserta status terbit dan jadwal tayangnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Artikel', href: postsIndex() },
                ]}
                actions={
                    <>
                        <Button asChild variant="filter">
                            <Link href={postCategoriesIndex()}>
                                <Tags />
                                Kelola Kategori
                            </Link>
                        </Button>
                        <Button asChild variant="create">
                            <Link href={postCreate()}>
                                <Plus />
                                Tulis Artikel
                            </Link>
                        </Button>
                    </>
                }
            />

            <DataTable
                rows={posts}
                columns={columns}
                filters={filters}
                rowId={(post) => post.id}
                reloadOnly={['posts', 'filters']}
                searchPlaceholder="Cari judul artikel…"
                emptyTitle="Belum ada artikel"
                emptyDescription="Tulis artikel pertama untuk mengisi halaman blog."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={postCreate()}>
                            <Plus />
                            Tulis Artikel
                        </Link>
                    </Button>
                }
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
                            <SelectTrigger className="w-full sm:w-40">
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
                            value={
                                filters.post_category_id === undefined ||
                                filters.post_category_id === ''
                                    ? 'all'
                                    : String(filters.post_category_id)
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'post_category_id',
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
                                        key={category.id}
                                        value={String(category.id)}
                                    >
                                        {category.name?.[LOCALE] ?? '—'}
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
                                    postsIndex().url,
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

            <ConfirmDialog
                open={publishing !== null}
                onOpenChange={(open) => !open && setPublishing(null)}
                loading={processing}
                tone="success"
                confirmVariant="publish"
                title={`Terbitkan "${publishing?.title?.[LOCALE] ?? ''}"?`}
                description="Artikel langsung tampil di halaman blog publik dan bisa diindeks mesin pencari."
                confirmLabel="Terbitkan Sekarang"
                loadingLabel="Menerbitkan..."
                onConfirm={() => {
                    if (!publishing) {
                        return;
                    }

                    setProcessing(true);
                    router.post(
                        publishPost(publishing.id).url,
                        {},
                        {
                            preserveScroll: true,
                            onFinish: () => {
                                setProcessing(false);
                                setPublishing(null);
                            },
                        },
                    );
                }}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus artikel "${deleting?.title?.[LOCALE] ?? ''}"?`}
                description="Artikel hilang dari halaman blog. Datanya dipindahkan ke arsip sehingga tautan lama masih bisa dipulihkan."
                confirmLabel="Hapus Artikel"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyPost(deleting.id).url, {
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
