import { router, usePage } from '@inertiajs/react';
import { FileText, FolderOpen, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';
import DateRangePicker from '@/components/form/date-range-picker';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import EmptyState from '@/components/shared/empty-state';
import InfoDialog from '@/components/shared/info-dialog';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { formatBytes } from '@/lib/upload-rules';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes/admin';
import {
    destroy as destroyMedia,
    index as mediaIndex,
    orphans as pruneOrphans,
} from '@/routes/admin/media';
import type { TableFilters } from '@/types/pagination';

type MediaUsage = {
    module: string;
    label: string;
};

type MediaFile = {
    path: string;
    name: string;
    directory: string;
    url: string;
    type: 'image' | 'document';
    extension: string;
    size: number;
    dimensions: { width: number; height: number } | null;
    modified_at: string;
    modified_date: string;
    usages: MediaUsage[];
};

type Props = {
    files: MediaFile[];
    summary: { total: number; orphans: number; size: number };
    filters: TableFilters;
};

export default function MediaIndex({ files, summary, filters }: Props) {
    const [search, setSearch] = useState(
        typeof filters.search === 'string' ? filters.search : '',
    );
    const [selected, setSelected] = useState<string[]>([]);
    const [detail, setDetail] = useState<MediaFile | null>(null);
    const [confirmingDelete, setConfirmingDelete] = useState(false);
    const [confirmingPrune, setConfirmingPrune] = useState(false);

    // Pesan "berkas masih dipakai" datang dari controller sebagai error
    // bernama `media`, bukan dari field form mana pun.
    const mediaError = usePage<{ errors: Record<string, string> }>().props
        .errors.media;

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            mediaIndex().url,
            { ...filters, [key]: value },
            { preserveState: true, replace: true },
        );

    const toggle = (path: string) =>
        setSelected((current) =>
            current.includes(path)
                ? current.filter((item) => item !== path)
                : [...current, path],
        );

    const selectedFiles = files.filter((file) => selected.includes(file.path));
    const blockedCount = selectedFiles.filter(
        (file) => file.usages.length > 0,
    ).length;

    return (
        <>
            <PageHeader
                title="Media Library"
                description="Seluruh berkas yang tersimpan beserta modul yang memakainya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Media Library', href: mediaIndex() },
                ]}
                actions={
                    <>
                        {selected.length > 0 && (
                            <Button
                                type="button"
                                variant="delete"
                                onClick={() => setConfirmingDelete(true)}
                            >
                                <Trash2 />
                                Hapus {selected.length} Berkas
                            </Button>
                        )}
                        {summary.orphans > 0 && (
                            <Button
                                type="button"
                                variant="archive"
                                onClick={() => setConfirmingPrune(true)}
                            >
                                <FolderOpen />
                                Hapus {summary.orphans} Berkas Tak Terpakai
                            </Button>
                        )}
                    </>
                }
            />

            {mediaError && (
                <div className="rounded-[var(--radius)] border border-s-[3px] border-destructive/25 border-s-destructive bg-destructive/10 px-3.5 py-2.5 text-[13px] text-destructive">
                    {mediaError}
                </div>
            )}

            <div className="grid gap-5 sm:grid-cols-3">
                {[
                    { label: 'Total berkas', value: String(summary.total) },
                    {
                        label: 'Tidak dipakai',
                        value: String(summary.orphans),
                    },
                    { label: 'Total ukuran', value: formatBytes(summary.size) },
                ].map((stat) => (
                    <div
                        key={stat.label}
                        className="rounded-card flex flex-col gap-2 border border-border bg-card p-5"
                    >
                        <span className="text-xs text-muted-foreground">
                            {stat.label}
                        </span>
                        <span className="text-2xl font-semibold tabular-nums">
                            {stat.value}
                        </span>
                    </div>
                ))}
            </div>

            <Panel
                title="Berkas"
                description="Klik kartu untuk melihat detail dan daftar pemakainya."
            >
                <div className="mb-4 flex flex-wrap items-center gap-2">
                    <div className="relative min-w-56 flex-1">
                        <Search
                            aria-hidden
                            className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            onKeyDown={(event) => {
                                if (event.key === 'Enter') {
                                    applyFilter('search', search || undefined);
                                }
                            }}
                            onBlur={() =>
                                applyFilter('search', search || undefined)
                            }
                            placeholder="Cari nama berkas…"
                            className="ps-9"
                        />
                    </div>

                    <Select
                        value={
                            typeof filters.type === 'string' &&
                            filters.type !== ''
                                ? filters.type
                                : 'all'
                        }
                        onValueChange={(value) =>
                            applyFilter(
                                'type',
                                value === 'all' ? undefined : value,
                            )
                        }
                    >
                        <SelectTrigger className="w-full sm:w-40">
                            <SelectValue placeholder="Semua tipe" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua tipe</SelectItem>
                            <SelectItem value="image">Gambar</SelectItem>
                            <SelectItem value="document">Dokumen</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select
                        value={
                            typeof filters.usage === 'string' &&
                            filters.usage !== ''
                                ? filters.usage
                                : 'all'
                        }
                        onValueChange={(value) =>
                            applyFilter(
                                'usage',
                                value === 'all' ? undefined : value,
                            )
                        }
                    >
                        <SelectTrigger className="w-full sm:w-44">
                            <SelectValue placeholder="Semua berkas" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua berkas</SelectItem>
                            <SelectItem value="used">Sedang dipakai</SelectItem>
                            <SelectItem value="orphan">
                                Tidak dipakai
                            </SelectItem>
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
                                mediaIndex().url,
                                {
                                    ...filters,
                                    from: range.from ?? undefined,
                                    to: range.to ?? undefined,
                                },
                                { preserveState: true, replace: true },
                            )
                        }
                    />
                </div>

                {files.length === 0 ? (
                    <EmptyState
                        variant="filtered"
                        title="Tidak ada berkas"
                        description="Belum ada berkas yang cocok dengan filter ini."
                    />
                ) : (
                    <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {files.map((file) => (
                            <li
                                key={file.path}
                                className={cn(
                                    'group flex flex-col overflow-hidden rounded-[var(--radius)] border border-border',
                                    selected.includes(file.path) &&
                                        'border-primary ring-1 ring-primary',
                                )}
                            >
                                <button
                                    type="button"
                                    onClick={() => setDetail(file)}
                                    className="flex h-32 items-center justify-center bg-muted"
                                >
                                    {file.type === 'image' ? (
                                        <img
                                            src={file.url}
                                            alt={file.name}
                                            className="size-full object-contain p-2"
                                        />
                                    ) : (
                                        <FileText
                                            aria-hidden
                                            className="size-8 text-muted-foreground"
                                        />
                                    )}
                                </button>

                                <div className="flex flex-1 flex-col gap-2 p-3">
                                    <div className="flex items-start gap-2">
                                        <Checkbox
                                            checked={selected.includes(
                                                file.path,
                                            )}
                                            onCheckedChange={() =>
                                                toggle(file.path)
                                            }
                                            aria-label={`Pilih ${file.name}`}
                                        />
                                        <span
                                            dir="ltr"
                                            className="line-clamp-2 flex-1 text-[13px] leading-tight break-all"
                                        >
                                            {file.name}
                                        </span>
                                    </div>

                                    <div className="flex flex-wrap items-center gap-1.5">
                                        <StatusBadge
                                            label={formatBytes(file.size)}
                                            tone="muted"
                                        />
                                        {file.usages.length === 0 ? (
                                            <StatusBadge
                                                label="Tidak dipakai"
                                                tone="warning"
                                            />
                                        ) : (
                                            <StatusBadge
                                                label={`${file.usages.length} pemakai`}
                                                tone="success"
                                            />
                                        )}
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </Panel>

            <InfoDialog
                open={detail !== null}
                onOpenChange={(open) => !open && setDetail(null)}
                tone="info"
                title={detail?.name ?? ''}
                description={
                    <div className="space-y-3">
                        {detail?.type === 'image' && (
                            <img
                                src={detail.url}
                                alt={detail.name}
                                className="max-h-56 w-full rounded-[var(--radius)] border border-border object-contain"
                            />
                        )}

                        <dl className="grid gap-2 text-[13px]">
                            {[
                                {
                                    label: 'Lokasi',
                                    value: detail?.directory || '/',
                                },
                                {
                                    label: 'Ukuran',
                                    value: formatBytes(detail?.size ?? 0),
                                },
                                {
                                    label: 'Dimensi',
                                    value: detail?.dimensions
                                        ? `${detail.dimensions.width} × ${detail.dimensions.height} piksel`
                                        : '—',
                                },
                                {
                                    label: 'Diubah',
                                    value: detail?.modified_at ?? '—',
                                },
                            ].map((row) => (
                                <div
                                    key={row.label}
                                    className="flex justify-between gap-3"
                                >
                                    <dt className="text-muted-foreground">
                                        {row.label}
                                    </dt>
                                    <dd
                                        dir="ltr"
                                        className="text-end break-all"
                                    >
                                        {row.value}
                                    </dd>
                                </div>
                            ))}
                        </dl>

                        <div className="space-y-1.5">
                            <p className="text-xs text-muted-foreground">
                                Dipakai oleh
                            </p>
                            {detail?.usages.length === 0 ? (
                                <p className="text-[13px]">
                                    Tidak ada baris yang memakai berkas ini —
                                    aman dihapus.
                                </p>
                            ) : (
                                <ul className="grid gap-1 text-[13px]">
                                    {detail?.usages.map((usage, index) => (
                                        <li
                                            key={`${usage.module}-${usage.label}-${index}`}
                                            className="flex justify-between gap-3"
                                        >
                                            <span>{usage.module}</span>
                                            <span className="text-muted-foreground">
                                                {usage.label}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </div>
                }
            />

            <ConfirmDialog
                open={confirmingDelete}
                onOpenChange={setConfirmingDelete}
                title={`Hapus ${selected.length} berkas terpilih?`}
                description={
                    blockedCount === 0
                        ? 'Berkas dihapus permanen dari penyimpanan dan tidak bisa dipulihkan.'
                        : `${blockedCount} dari ${selected.length} berkas masih dipakai dan akan ditolak. Sisanya dihapus permanen.`
                }
                confirmLabel="Hapus Berkas"
                onConfirm={() => {
                    router.delete(destroyMedia().url, {
                        data: { paths: selected },
                        preserveScroll: true,
                        onFinish: () => setSelected([]),
                    });
                    setConfirmingDelete(false);
                }}
            />

            <ConfirmDialog
                open={confirmingPrune}
                onOpenChange={setConfirmingPrune}
                tone="warning"
                title={`Hapus ${summary.orphans} berkas yang tidak dipakai?`}
                description="Berkas ini tidak dipakai produk, artikel, maupun modul lain. Dihapus permanen dan tidak bisa dipulihkan. Berkas yang masih dipakai tidak ikut terhapus."
                confirmLabel="Hapus Sekarang"
                onConfirm={() => {
                    router.delete(pruneOrphans().url, { preserveScroll: true });
                    setConfirmingPrune(false);
                }}
            />
        </>
    );
}
