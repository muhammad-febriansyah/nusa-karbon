import { Link, router } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';
import { DataTable } from '@/components/data-table/data-table';
import type { DataTableColumn } from '@/components/data-table/data-table';
import {
    DeleteButton,
    DetailButton,
    EditButton,
} from '@/components/shared/action-buttons';
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
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { dashboard } from '@/routes/admin';
import {
    create as userCreate,
    destroy as destroyUser,
    edit as userEdit,
    index as usersIndex,
    show as userShow,
} from '@/routes/admin/users';
import type { AdminUser } from '@/types/models';
import type { Paginated, TableFilters } from '@/types/pagination';

const initials = (name: string) =>
    name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');

type Props = {
    users: Paginated<AdminUser>;
    currentUserId: number;
    filters: TableFilters;
};

export default function UserIndex({ users, currentUserId, filters }: Props) {
    const [deleting, setDeleting] = useState<AdminUser | null>(null);
    const [processing, setProcessing] = useState(false);

    const applyFilter = (key: string, value: string | undefined) =>
        router.get(
            usersIndex().url,
            { ...filters, [key]: value, page: 1 },
            { preserveState: true, replace: true },
        );

    const columns: DataTableColumn<AdminUser>[] = [
        {
            id: 'name',
            header: 'Pengguna',
            sortKey: 'name',
            cell: (user) => (
                <div className="flex items-center gap-3">
                    {user.avatar_url ? (
                        <img
                            src={user.avatar_url}
                            alt=""
                            className="size-9 shrink-0 rounded-full object-cover"
                        />
                    ) : (
                        <span
                            aria-hidden
                            className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/12 text-xs font-semibold text-primary"
                        >
                            {initials(user.name)}
                        </span>
                    )}
                    <div className="flex flex-col leading-tight">
                        <span className="font-medium">{user.name}</span>
                        <span
                            dir="ltr"
                            className="text-xs text-muted-foreground"
                        >
                            {user.email}
                        </span>
                    </div>
                </div>
            ),
        },
        {
            id: 'phone',
            header: 'Telepon',
            hiddenByDefault: true,
            cell: (user) => <span dir="ltr">{user.phone ?? '—'}</span>,
        },
        {
            id: 'last_login_at',
            header: 'Login terakhir',
            sortKey: 'last_login_at',
            cell: (user) => (
                <span className="tabular-nums">
                    {user.last_login_at ?? '—'}
                </span>
            ),
        },
        {
            id: 'is_active',
            header: 'Status',
            sortKey: 'is_active',
            cell: (user) => (
                <div className="flex items-center gap-2">
                    <StatusBadge
                        label={user.is_active ? 'Aktif' : 'Nonaktif'}
                        tone={user.is_active ? 'success' : 'muted'}
                    />
                    {user.two_factor_enabled && (
                        <StatusBadge label="2FA" tone="info" />
                    )}
                </div>
            ),
        },
        {
            id: 'actions',
            header: 'Aksi',
            sticky: true,
            cell: (user) => {
                const isSelf = user.id === currentUserId;

                return (
                    <div className="flex items-center justify-end gap-2">
                        <DetailButton href={userShow(user.id)} />
                        <EditButton href={userEdit(user.id)} />
                        {isSelf ? (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <span>
                                        <DeleteButton disabled />
                                    </span>
                                </TooltipTrigger>
                                <TooltipContent>
                                    Akun sendiri tidak dapat dihapus.
                                </TooltipContent>
                            </Tooltip>
                        ) : (
                            <DeleteButton onClick={() => setDeleting(user)} />
                        )}
                    </div>
                );
            },
        },
    ];

    return (
        <>
            <PageHeader
                title="Manajemen Pengguna"
                description="Akun yang boleh masuk ke panel admin."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Manajemen Pengguna', href: usersIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={userCreate()}>
                            <Plus />
                            Tambah Pengguna
                        </Link>
                    </Button>
                }
            />

            <DataTable
                rows={users}
                columns={columns}
                filters={filters}
                rowId={(user) => user.id}
                reloadOnly={['users', 'filters']}
                searchPlaceholder="Cari nama atau email…"
                emptyTitle="Belum ada pengguna"
                emptyDescription="Tambahkan akun untuk rekan yang perlu mengakses panel admin."
                emptyAction={
                    <Button asChild variant="create">
                        <Link href={userCreate()}>
                            <Plus />
                            Tambah Pengguna
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
                title={`Hapus pengguna "${deleting?.name ?? ''}"?`}
                description="Akun kehilangan akses ke panel admin. Jejaknya di log aktivitas dan catatan RFQ tetap tersimpan."
                confirmLabel="Hapus Pengguna"
                loadingLabel="Menghapus..."
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    setProcessing(true);
                    router.delete(destroyUser(deleting.id).url, {
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
