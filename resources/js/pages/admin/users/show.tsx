import { Link, router, usePage } from '@inertiajs/react';
import { KeyRound, Power, SquarePen } from 'lucide-react';
import { useState } from 'react';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { dashboard } from '@/routes/admin';
import {
    active as toggleActive,
    edit as userEdit,
    index as usersIndex,
    passwordLink as sendPasswordLink,
    show as userShow,
} from '@/routes/admin/users';
import type { Auth } from '@/types';
import type { ActivityLogEntry, AdminUser } from '@/types/models';

const EVENT_LABELS: Record<string, string> = {
    created: 'Dibuat',
    updated: 'Diubah',
    deleted: 'Dihapus',
};

type Props = {
    user: AdminUser;
    handledRfqs: number;
    activities: ActivityLogEntry[];
};

export default function UserShow({ user, handledRfqs, activities }: Props) {
    const page = usePage<{ auth: Auth }>();
    const [confirmingDeactivate, setConfirmingDeactivate] = useState(false);

    const isSelf = page.props.auth.user?.id === user.id;

    const facts = [
        { label: 'Email', value: user.email, ltr: true },
        { label: 'Telepon', value: user.phone ?? '—', ltr: true },
        {
            label: 'Verifikasi email',
            value: user.email_verified ? 'Terverifikasi' : 'Belum',
            ltr: false,
        },
        {
            label: 'Autentikasi dua faktor',
            value: user.two_factor_enabled ? 'Aktif' : 'Nonaktif',
            ltr: false,
        },
        {
            label: 'Login terakhir',
            value: user.last_login_at ?? '—',
            ltr: false,
        },
        { label: 'IP terakhir', value: user.last_login_ip ?? '—', ltr: true },
        { label: 'RFQ ditangani', value: String(handledRfqs), ltr: false },
        { label: 'Dibuat', value: user.created_at ?? '—', ltr: false },
    ];

    return (
        <>
            <PageHeader
                title={user.name}
                description="Detail akun, status keamanan, dan jejak aktivitas terakhir."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Manajemen Pengguna', href: usersIndex() },
                    { title: user.name, href: userShow(user.id) },
                ]}
                actions={
                    <>
                        <Button
                            type="button"
                            variant="filter"
                            onClick={() =>
                                router.post(
                                    sendPasswordLink(user.id).url,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <KeyRound />
                            Kirim Tautan Password
                        </Button>

                        {isSelf ? (
                            <Tooltip>
                                <TooltipTrigger asChild>
                                    <span>
                                        <Button
                                            type="button"
                                            variant="archive"
                                            disabled
                                        >
                                            <Power />
                                            Nonaktifkan
                                        </Button>
                                    </span>
                                </TooltipTrigger>
                                <TooltipContent>
                                    Akun sendiri tidak dapat dinonaktifkan.
                                </TooltipContent>
                            </Tooltip>
                        ) : (
                            <Button
                                type="button"
                                variant={user.is_active ? 'archive' : 'toggle'}
                                onClick={() =>
                                    user.is_active
                                        ? setConfirmingDeactivate(true)
                                        : router.put(
                                              toggleActive(user.id).url,
                                              { is_active: true },
                                              { preserveScroll: true },
                                          )
                                }
                            >
                                <Power />
                                {user.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                            </Button>
                        )}

                        <Button asChild variant="edit">
                            <Link href={userEdit(user.id)}>
                                <SquarePen />
                                Ubah
                            </Link>
                        </Button>
                    </>
                }
            />

            <div className="grid gap-5 lg:grid-cols-3">
                <Panel className="lg:col-span-1" title="Profil">
                    <div className="flex items-center gap-3">
                        {user.avatar_url ? (
                            <img
                                src={user.avatar_url}
                                alt=""
                                className="size-14 rounded-full object-cover"
                            />
                        ) : (
                            <span
                                aria-hidden
                                className="flex size-14 items-center justify-center rounded-full bg-primary/12 text-sm font-semibold text-primary"
                            >
                                {user.name.charAt(0).toUpperCase()}
                            </span>
                        )}
                        <div className="space-y-1">
                            <p className="font-medium">{user.name}</p>
                            <StatusBadge
                                label={user.is_active ? 'Aktif' : 'Nonaktif'}
                                tone={user.is_active ? 'success' : 'muted'}
                            />
                        </div>
                    </div>

                    <dl className="mt-5 grid gap-3">
                        {facts.map((fact) => (
                            <div key={fact.label} className="space-y-0.5">
                                <dt className="text-xs text-muted-foreground">
                                    {fact.label}
                                </dt>
                                <dd
                                    dir={fact.ltr ? 'ltr' : undefined}
                                    className="text-sm break-all"
                                >
                                    {fact.value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </Panel>

                <Panel
                    className="lg:col-span-2"
                    title="Aktivitas Terakhir"
                    description="Dua puluh perubahan terakhir yang dilakukan akun ini."
                >
                    {activities.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Belum ada aktivitas tercatat.
                        </p>
                    ) : (
                        <ol className="space-y-3 border-s border-border ps-4">
                            {activities.map((activity) => (
                                <li key={activity.id} className="space-y-0.5">
                                    <p className="text-sm">
                                        {activity.subject_label ?? 'Objek'}{' '}
                                        {EVENT_LABELS[activity.event ?? ''] ??
                                            activity.event}
                                        {activity.subject_id !== null &&
                                            ` #${activity.subject_id}`}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                        {activity.created_at}
                                    </p>
                                </li>
                            ))}
                        </ol>
                    )}
                </Panel>
            </div>

            <ConfirmDialog
                open={confirmingDeactivate}
                onOpenChange={setConfirmingDeactivate}
                tone="warning"
                confirmVariant="archive"
                title={`Nonaktifkan ${user.name}?`}
                description="Akun langsung ditolak saat mencoba masuk. Datanya tetap tersimpan dan bisa diaktifkan kembali."
                confirmLabel="Nonaktifkan Akun"
                onConfirm={() => {
                    router.put(
                        toggleActive(user.id).url,
                        { is_active: false },
                        { preserveScroll: true },
                    );
                    setConfirmingDeactivate(false);
                }}
            />
        </>
    );
}
