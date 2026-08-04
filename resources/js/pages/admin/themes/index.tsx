import { Link, router } from '@inertiajs/react';
import { Copy, Globe, Lock, MonitorCog, Plus } from 'lucide-react';
import { useState } from 'react';
import { DeleteButton, EditButton } from '@/components/shared/action-buttons';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import EmptyState from '@/components/shared/empty-state';
import PageHeader from '@/components/shared/page-header';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { dashboard } from '@/routes/admin';
import {
    activate as activateTheme,
    create as themeCreate,
    destroy as destroyTheme,
    duplicate as duplicateTheme,
    edit as themeEdit,
    index as themesIndex,
} from '@/routes/admin/themes';
import type { SelectOption, Theme } from '@/types/models';
import ThemePreviewCard from './theme-preview-card';

type Props = {
    themes: Theme[];
    scopes: SelectOption[];
};

type Activation = {
    theme: Theme;
    scope: 'admin' | 'public';
};

const SCOPE_LABELS: Record<string, string> = {
    admin: 'Panel admin',
    public: 'Website publik',
    both: 'Panel admin & website publik',
};

/**
 * Daftar tema berupa kartu pratinjau, bukan tabel: nama palet tidak berarti
 * apa-apa bagi admin non-teknis — yang menentukan pilihan adalah tampilannya.
 */
export default function ThemeIndex({ themes }: Props) {
    const [deleting, setDeleting] = useState<Theme | null>(null);
    const [activating, setActivating] = useState<Activation | null>(null);

    return (
        <>
            <PageHeader
                title="Tema & Warna"
                description="Pilih palet untuk panel admin dan website publik. Setiap kartu menampilkan tampilan aslinya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tema & Warna', href: themesIndex() },
                ]}
                actions={
                    <Button asChild variant="create">
                        <Link href={themeCreate()}>
                            <Plus />
                            Tambah Tema
                        </Link>
                    </Button>
                }
            />

            {themes.length === 0 ? (
                <div className="rounded-card border border-border bg-card p-6">
                    <EmptyState
                        title="Belum ada tema"
                        description="Jalankan seeder preset atau buat tema baru."
                        action={
                            <Button asChild variant="create">
                                <Link href={themeCreate()}>
                                    <Plus />
                                    Tambah Tema
                                </Link>
                            </Button>
                        }
                    />
                </div>
            ) : (
                <div className="grid gap-5 xl:grid-cols-2">
                    {themes.map((theme) => (
                        <section
                            key={theme.id}
                            className="rounded-card flex flex-col gap-4 border border-border bg-card p-5"
                        >
                            <header className="flex flex-wrap items-start justify-between gap-3">
                                <div className="space-y-1">
                                    <h2 className="text-base font-semibold">
                                        {theme.name}
                                    </h2>
                                    <p className="text-[13px] text-muted-foreground">
                                        {SCOPE_LABELS[theme.scope] ??
                                            theme.scope}
                                    </p>
                                </div>

                                <div className="flex flex-wrap items-center gap-2">
                                    {theme.is_preset && (
                                        <StatusBadge
                                            label="Preset"
                                            tone="neutral"
                                        />
                                    )}
                                    {theme.is_active_admin && (
                                        <StatusBadge
                                            label="Aktif di admin"
                                            tone="success"
                                        />
                                    )}
                                    {theme.is_active_public && (
                                        <StatusBadge
                                            label="Aktif di publik"
                                            tone="info"
                                        />
                                    )}
                                </div>
                            </header>

                            {/* Pratinjau memakai warna tema ini sendiri, bukan
                                token global, sehingga bisa dibandingkan
                                berdampingan tanpa mengaktifkannya dulu. */}
                            <ThemePreviewCard tokens={theme.tokens} />

                            <footer className="flex flex-wrap items-center gap-2">
                                {theme.scope !== 'public' &&
                                    !theme.is_active_admin && (
                                        <Button
                                            type="button"
                                            variant="toggle"
                                            size="sm"
                                            onClick={() =>
                                                setActivating({
                                                    theme,
                                                    scope: 'admin',
                                                })
                                            }
                                        >
                                            <MonitorCog />
                                            Pakai di Admin
                                        </Button>
                                    )}

                                {theme.scope !== 'admin' &&
                                    !theme.is_active_public && (
                                        <Button
                                            type="button"
                                            variant="publish"
                                            size="sm"
                                            onClick={() =>
                                                setActivating({
                                                    theme,
                                                    scope: 'public',
                                                })
                                            }
                                        >
                                            <Globe />
                                            Pakai di Publik
                                        </Button>
                                    )}

                                <Button
                                    type="button"
                                    variant="duplicate"
                                    size="sm"
                                    onClick={() =>
                                        router.post(
                                            duplicateTheme(theme.id).url,
                                        )
                                    }
                                >
                                    <Copy />
                                    Duplikat
                                </Button>

                                {theme.is_preset ? (
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <span className="flex items-center gap-2">
                                                <EditButton disabled />
                                                <DeleteButton disabled />
                                            </span>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <span className="flex items-center gap-1.5">
                                                <Lock
                                                    aria-hidden
                                                    className="size-3.5"
                                                />
                                                Preset bawaan terkunci. Duplikat
                                                untuk mengubahnya.
                                            </span>
                                        </TooltipContent>
                                    </Tooltip>
                                ) : (
                                    <>
                                        <EditButton
                                            href={themeEdit(theme.id)}
                                        />
                                        <DeleteButton
                                            onClick={() => setDeleting(theme)}
                                        />
                                    </>
                                )}
                            </footer>
                        </section>
                    ))}
                </div>
            )}

            <ConfirmDialog
                open={activating !== null}
                onOpenChange={(open) => !open && setActivating(null)}
                tone="success"
                confirmVariant="toggle"
                title={
                    activating === null
                        ? ''
                        : `Pakai tema "${activating.theme.name}" untuk ${
                              activating.scope === 'admin'
                                  ? 'Panel Admin'
                                  : 'Website Publik'
                          }?`
                }
                description={
                    activating?.scope === 'admin'
                        ? 'Seluruh pengguna admin langsung melihat palet baru pada halaman berikutnya yang dibuka.'
                        : 'Pengunjung website langsung melihat palet baru pada halaman berikutnya yang dimuat.'
                }
                confirmLabel="Pakai Tema Ini"
                onConfirm={() => {
                    if (!activating) {
                        return;
                    }

                    router.put(
                        activateTheme(activating.theme.id).url,
                        { scope: activating.scope },
                        { preserveScroll: true },
                    );
                    setActivating(null);
                }}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={`Hapus tema "${deleting?.name ?? ''}"?`}
                description="Tema dipindahkan ke arsip. Tema yang sedang dipakai tidak dapat dihapus."
                confirmLabel="Hapus Tema"
                onConfirm={() => {
                    if (!deleting) {
                        return;
                    }

                    router.delete(destroyTheme(deleting.id).url, {
                        preserveScroll: true,
                    });
                    setDeleting(null);
                }}
            />
        </>
    );
}
