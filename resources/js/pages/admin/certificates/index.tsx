import { Link, router } from '@inertiajs/react';
import { AlertTriangle, Plus } from 'lucide-react';
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
    create as certificateCreate,
    destroy as destroyCertificate,
    edit as certificateEdit,
    index as certificatesIndex,
} from '@/routes/admin/certificates';
import type { Certificate } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const LOCALE = 'id';

const formatDate = (value: string | null) =>
    value === null
        ? '—'
        : new Date(value).toLocaleDateString('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
          });

type Props = {
    certificates: Paginated<Certificate>;
    expiringCount: number;
    filters: TableFilters;
};

export default function CertificateIndex({
    certificates,
    expiringCount,
    filters,
}: Props) {
    const [deleting, setDeleting] = useState<Certificate | null>(null);
    const [processing, setProcessing] = useState(false);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            certificatesIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<Certificate>[] = [
        {
            id: 'name',
            header: 'Sertifikat',
            sortKey: 'sort_order',
            cell: (certificate) => (
                <div className="flex items-center gap-3">
                    {certificate.logo_url ? (
                        <img
                            src={certificate.logo_url}
                            alt=""
                            className="size-9 shrink-0 rounded-[var(--radius)] border border-border object-contain p-1"
                        />
                    ) : (
                        <span
                            aria-hidden
                            className="size-9 shrink-0 rounded-[var(--radius)] border border-dashed border-border"
                        />
                    )}
                    <div className="flex flex-col leading-tight">
                        <span className="font-medium">
                            {certificate.name?.[LOCALE] ?? '—'}
                        </span>
                        <span className="text-xs text-muted-foreground">
                            {certificate.issuer ?? 'Penerbit belum diisi'}
                        </span>
                    </div>
                </div>
            ),
        },
        {
            id: 'certificate_number',
            header: 'Nomor',
            hiddenByDefault: true,
            cell: (certificate) => (
                <span dir="ltr" className="text-muted-foreground tabular-nums">
                    {certificate.certificate_number ?? '—'}
                </span>
            ),
        },
        {
            id: 'issued_at',
            header: 'Terbit',
            sortKey: 'issued_at',
            hiddenByDefault: true,
            cell: (certificate) => (
                <span className="tabular-nums">
                    {formatDate(certificate.issued_at)}
                </span>
            ),
        },
        {
            id: 'expires_at',
            header: 'Kedaluwarsa',
            sortKey: 'expires_at',
            cell: (certificate) => (
                <div className="flex items-center gap-2">
                    <span className="tabular-nums">
                        {formatDate(certificate.expires_at)}
                    </span>
                    {certificate.is_expired && (
                        <StatusBadge label="Kedaluwarsa" tone="destructive" />
                    )}
                    {certificate.is_expiring_soon && (
                        <StatusBadge label="Segera habis" tone="warning" />
                    )}
                </div>
            ),
        },
        {
            id: 'show_in_trust_bar',
            header: 'Trust bar',
            cell: (certificate) => (
                <StatusBadge
                    label={certificate.show_in_trust_bar ? 'Tampil' : 'Tidak'}
                    tone={certificate.show_in_trust_bar ? 'info' : 'muted'}
                />
            ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (certificate) => (
                <StatusBadge
                    label={certificate.is_active ? 'Aktif' : 'Nonaktif'}
                    tone={certificate.is_active ? 'success' : 'muted'}
                />
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (certificate) => (
                <DataTableRowActions
                    editHref={certificateEdit(certificate.id)}
                    onDelete={() => setDeleting(certificate)}
                />
            ),
        },
    ];

    return (
        <>
            <PageHeader
                title="Sertifikat"
                description="Kelola sertifikat perusahaan beserta masa berlaku dan berkas scan-nya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Sertifikat', href: certificatesIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={certificateCreate()}>
                            <Plus />
                            Tambah Sertifikat
                        </Link>
                    </Button>
                }
            />

            {expiringCount > 0 && (
                <div className="flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-warning/25 border-s-warning bg-warning/10 px-3.5 py-2.5">
                    <AlertTriangle
                        aria-hidden
                        className="mt-0.5 size-4 shrink-0 text-warning"
                    />
                    <p className="text-[13px] text-warning">
                        {expiringCount} sertifikat kedaluwarsa dalam 60 hari ke
                        depan. Urus perpanjangannya sebelum buyer menanyakannya.
                    </p>
                </div>
            )}

            <DataTable
                rows={certificates}
                columns={columns}
                filters={filters}
                rowId={(certificate) => certificate.id}
                reloadOnly={['certificates', 'expiringCount', 'filters']}
                searchPlaceholder="Cari nama, penerbit, atau nomor…"
                emptyTitle="Belum ada sertifikat"
                emptyDescription="Tambahkan sertifikat mutu dan dokumen ekspor agar buyer bisa memverifikasinya."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={certificateCreate()}>
                            <Plus />
                            Tambah Sertifikat
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
                                typeof filters.expiry === 'string' &&
                                filters.expiry !== ''
                                    ? filters.expiry
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilter(
                                    'expiry',
                                    value === 'all' ? undefined : value,
                                )
                            }
                        >
                            <SelectTrigger className="w-full sm:w-44">
                                <SelectValue placeholder="Semua masa berlaku" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Semua masa berlaku
                                </SelectItem>
                                <SelectItem value="expiring">
                                    Kedaluwarsa ≤ 60 hari
                                </SelectItem>
                                <SelectItem value="expired">
                                    Sudah kedaluwarsa
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </>
                }
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                loading={processing}
                title={`Hapus sertifikat "${deleting?.name?.[LOCALE] ?? ''}"?`}
                description="Sertifikat dipindahkan ke arsip dan hilang dari halaman publik beserta trust bar. Berkas scan tetap tersimpan."
                confirmLabel="Hapus Sertifikat"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyCertificate(deleting.id).url, {
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
