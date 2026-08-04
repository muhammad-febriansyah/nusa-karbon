import { router, useForm } from '@inertiajs/react';
import {
    Copy,
    MailWarning,
    MessageCircle,
    Save,
    ShieldAlert,
} from 'lucide-react';
import { useState } from 'react';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import StatusBadge from '@/components/shared/status-badge';
import type { StatusTone } from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { dashboard } from '@/routes/admin';
import {
    index as rfqsIndex,
    show as rfqShow,
    spam as markSpam,
    status as updateStatus,
    unread as markUnread,
} from '@/routes/admin/rfqs';
import { store as storeNote } from '@/routes/admin/rfqs/notes';
import type { RfqRequest, SelectOption } from '@/types/models';

type Props = {
    rfq: RfqRequest;
    statuses: SelectOption[];
};

type Field = {
    label: string;
    value: string | null;
    ltr?: boolean;
};

export default function RfqShow({ rfq, statuses }: Props) {
    const [confirmingSpam, setConfirmingSpam] = useState(false);
    const [copied, setCopied] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        note: '',
    });

    const fields: Field[] = [
        { label: 'Perusahaan', value: rfq.company_name },
        { label: 'Nama kontak', value: rfq.contact_name },
        { label: 'Email', value: rfq.email, ltr: true },
        { label: 'Telepon', value: rfq.phone, ltr: true },
        { label: 'WhatsApp', value: rfq.whatsapp, ltr: true },
        { label: 'Negara', value: rfq.country_code, ltr: true },
        { label: 'Incoterms', value: rfq.incoterms, ltr: true },
        { label: 'Pelabuhan tujuan', value: rfq.port_of_destination },
        { label: 'Perkiraan volume', value: rfq.monthly_tonnage },
        { label: 'Produk diminati', value: rfq.product_name ?? null },
        {
            label: 'Tipe kontainer',
            value: rfq.container_code ?? null,
            ltr: true,
        },
    ];

    const metadata: Field[] = [
        { label: 'Bahasa saat submit', value: rfq.locale, ltr: true },
        { label: 'Halaman asal', value: rfq.source_url, ltr: true },
        { label: 'Alamat IP', value: rfq.ip_address, ltr: true },
        { label: 'Waktu masuk', value: rfq.created_at },
    ];

    const copyEmail = async () => {
        await navigator.clipboard.writeText(rfq.email);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 2000);
    };

    return (
        <>
            <PageHeader
                title={rfq.reference_no}
                description={`Permintaan dari ${rfq.company_name} (${rfq.country_code}).`}
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'RFQ Inbox', href: rfqsIndex() },
                    { title: rfq.reference_no, href: rfqShow(rfq.id) },
                ]}
                actions={
                    <>
                        {rfq.whatsapp_link && (
                            <Button asChild variant="whatsapp">
                                <a
                                    href={rfq.whatsapp_link}
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    <MessageCircle />
                                    Buka WhatsApp
                                </a>
                            </Button>
                        )}
                        <Button
                            type="button"
                            variant="filter"
                            onClick={copyEmail}
                        >
                            <Copy />
                            {copied ? 'Email disalin' : 'Salin Email'}
                        </Button>
                        <Button
                            type="button"
                            variant="archive"
                            onClick={() =>
                                router.put(
                                    markUnread(rfq.id).url,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <MailWarning />
                            Tandai Belum Dibaca
                        </Button>
                    </>
                }
            />

            <div className="grid gap-5 lg:grid-cols-3">
                <div className="space-y-5 lg:col-span-2">
                    <Panel
                        title="Data Permintaan"
                        description="Isian buyer apa adanya, tanpa penyuntingan."
                    >
                        <dl className="grid gap-4 sm:grid-cols-2">
                            {fields.map((field) => (
                                <div key={field.label} className="space-y-0.5">
                                    <dt className="text-xs text-muted-foreground">
                                        {field.label}
                                    </dt>
                                    <dd
                                        dir={field.ltr ? 'ltr' : undefined}
                                        className="text-sm"
                                    >
                                        {field.value ?? '—'}
                                    </dd>
                                </div>
                            ))}
                        </dl>

                        {rfq.packaging_notes && (
                            <div className="mt-5 space-y-0.5">
                                <p className="text-xs text-muted-foreground">
                                    Catatan kemasan
                                </p>
                                <p className="text-sm whitespace-pre-line">
                                    {rfq.packaging_notes}
                                </p>
                            </div>
                        )}

                        {rfq.message && (
                            <div className="mt-5 space-y-0.5">
                                <p className="text-xs text-muted-foreground">
                                    Pesan buyer
                                </p>
                                <p className="text-sm whitespace-pre-line">
                                    {rfq.message}
                                </p>
                            </div>
                        )}
                    </Panel>

                    <Panel
                        title="Catatan Tindak Lanjut"
                        description="Riwayat komunikasi internal soal permintaan ini."
                    >
                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                post(storeNote(rfq.id).url, {
                                    preserveScroll: true,
                                    onSuccess: () => reset('note'),
                                });
                            }}
                            className="grid gap-2"
                        >
                            <Label htmlFor="note">Catatan baru</Label>
                            <Textarea
                                id="note"
                                rows={3}
                                value={data.note}
                                onChange={(event) =>
                                    setData('note', event.target.value)
                                }
                                placeholder="Contoh: sudah dikirim penawaran FOB Surabaya, menunggu balasan."
                                aria-invalid={Boolean(errors.note)}
                            />
                            {errors.note && (
                                <p className="text-xs text-destructive">
                                    {errors.note}
                                </p>
                            )}
                            <div className="flex justify-end">
                                <Button
                                    type="submit"
                                    variant="save"
                                    size="sm"
                                    disabled={processing || data.note === ''}
                                >
                                    {processing ? <Spinner /> : <Save />}
                                    Simpan Catatan
                                </Button>
                            </div>
                        </form>

                        {rfq.notes && rfq.notes.length > 0 && (
                            <ol className="mt-5 space-y-3 border-s border-border ps-4">
                                {rfq.notes.map((note) => (
                                    <li key={note.id} className="space-y-0.5">
                                        <p className="text-sm whitespace-pre-line">
                                            {note.note}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {note.user_name ?? 'Sistem'} ·{' '}
                                            {note.created_at}
                                        </p>
                                    </li>
                                ))}
                            </ol>
                        )}
                    </Panel>
                </div>

                <div className="space-y-5">
                    <Panel title="Status">
                        <div className="grid gap-3">
                            <StatusBadge
                                label={rfq.status_label}
                                tone={rfq.status_color as StatusTone}
                            />

                            <div className="grid gap-1.5">
                                <Label htmlFor="status">Ubah status</Label>
                                <Select
                                    value={rfq.status}
                                    onValueChange={(value) =>
                                        router.put(
                                            updateStatus(rfq.id).url,
                                            { status: value },
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    <SelectTrigger id="status">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
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
                            </div>

                            <Button
                                type="button"
                                variant="delete"
                                onClick={() => setConfirmingSpam(true)}
                            >
                                <ShieldAlert />
                                Tandai Spam
                            </Button>
                        </div>
                    </Panel>

                    <Panel title="Metadata">
                        <dl className="grid gap-3">
                            {metadata.map((field) => (
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
                </div>
            </div>

            <ConfirmDialog
                open={confirmingSpam}
                onOpenChange={setConfirmingSpam}
                title={`Tandai ${rfq.reference_no} sebagai spam?`}
                description="Permintaan hilang dari daftar utama tapi tetap tersimpan agar pola pengirimnya bisa ditelusuri."
                confirmLabel="Tandai Spam"
                onConfirm={() => {
                    router.put(
                        markSpam(rfq.id).url,
                        {},
                        { preserveScroll: true },
                    );
                    setConfirmingSpam(false);
                }}
            />
        </>
    );
}
