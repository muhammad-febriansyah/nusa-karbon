import { router } from '@inertiajs/react';
import { MailWarning, ShieldAlert, Trash2 } from 'lucide-react';
import { useState } from 'react';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/admin';
import {
    destroy as destroyMessage,
    index as messagesIndex,
    show as messageShow,
    update as updateMessage,
} from '@/routes/admin/contact-messages';
import type { ContactMessage } from '@/types/models';

type Props = {
    message: ContactMessage;
};

export default function ContactMessageShow({ message }: Props) {
    const [deleting, setDeleting] = useState(false);

    const fields = [
        { label: 'Nama', value: message.name, ltr: false },
        { label: 'Email', value: message.email, ltr: true },
        { label: 'Telepon', value: message.phone, ltr: true },
        { label: 'Subjek', value: message.subject, ltr: false },
        { label: 'Bahasa', value: message.locale, ltr: true },
        { label: 'Alamat IP', value: message.ip_address, ltr: true },
        { label: 'Waktu masuk', value: message.created_at, ltr: false },
    ];

    return (
        <>
            <PageHeader
                title={message.subject ?? `Pesan dari ${message.name}`}
                description="Pesan dari form kontak halaman publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Pesan Kontak', href: messagesIndex() },
                    {
                        title: message.name,
                        href: messageShow(message.id),
                    },
                ]}
                actions={
                    <>
                        <Button
                            type="button"
                            variant="archive"
                            onClick={() =>
                                router.put(
                                    updateMessage(message.id).url,
                                    { is_read: false },
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <MailWarning />
                            Tandai Belum Dibaca
                        </Button>
                        {!message.is_spam && (
                            <Button
                                type="button"
                                variant="filter"
                                onClick={() =>
                                    router.put(
                                        updateMessage(message.id).url,
                                        { is_spam: true },
                                        { preserveScroll: true },
                                    )
                                }
                            >
                                <ShieldAlert />
                                Tandai Spam
                            </Button>
                        )}
                        <Button
                            type="button"
                            variant="delete"
                            onClick={() => setDeleting(true)}
                        >
                            <Trash2 />
                            Hapus
                        </Button>
                    </>
                }
            />

            <Panel title="Isi Pesan">
                <div className="mb-4 flex items-center gap-2">
                    <StatusBadge
                        label={message.is_read ? 'Dibaca' : 'Baru'}
                        tone={message.is_read ? 'muted' : 'info'}
                    />
                    {message.is_spam && (
                        <StatusBadge label="Spam" tone="destructive" />
                    )}
                </div>

                <p className="text-sm whitespace-pre-line">{message.message}</p>
            </Panel>

            <Panel title="Data Pengirim">
                <dl className="grid gap-4 sm:grid-cols-3">
                    {fields.map((field) => (
                        <div key={field.label} className="space-y-0.5">
                            <dt className="text-xs text-muted-foreground">
                                {field.label}
                            </dt>
                            <dd
                                dir={field.ltr ? 'ltr' : undefined}
                                className="text-sm break-all"
                            >
                                {field.value ?? '—'}
                            </dd>
                        </div>
                    ))}
                </dl>
            </Panel>

            <ConfirmDialog
                open={deleting}
                onOpenChange={setDeleting}
                title={`Hapus pesan dari "${message.name}"?`}
                description="Pesan dihapus permanen dan tidak bisa dipulihkan."
                confirmLabel="Hapus Pesan"
                onConfirm={() => {
                    router.delete(destroyMessage(message.id).url);
                    setDeleting(false);
                }}
            />
        </>
    );
}
