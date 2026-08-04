import { Link, useForm } from '@inertiajs/react';
import { ExternalLink, Save, Trash2, X } from 'lucide-react';
import DatePicker from '@/components/form/date-picker';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import DocumentUploader from '@/components/upload/document-uploader';
import ImageUploader from '@/components/upload/image-uploader';
import {
    index as certificatesIndex,
    store as storeCertificate,
    update as updateCertificate,
} from '@/routes/admin/certificates';
import type { Certificate, Translated } from '@/types/models';

const LOCALE = 'id';

type Props = {
    certificate: Certificate | null;
};

type CertificateFormData = {
    name: Translated;
    description: Translated;
    issuer: string;
    certificate_number: string;
    issued_at: string | null;
    expires_at: string | null;
    logo_token: string | null;
    remove_logo: boolean;
    file_token: string | null;
    remove_file: boolean;
    show_in_trust_bar: boolean;
    is_active: boolean;
};

const toForm = (certificate: Certificate | null): CertificateFormData => ({
    name: { [LOCALE]: certificate?.name?.[LOCALE] ?? '' },
    description: { [LOCALE]: certificate?.description?.[LOCALE] ?? '' },
    issuer: certificate?.issuer ?? '',
    certificate_number: certificate?.certificate_number ?? '',
    issued_at: certificate?.issued_at ?? null,
    expires_at: certificate?.expires_at ?? null,
    logo_token: null,
    remove_logo: false,
    file_token: null,
    remove_file: false,
    show_in_trust_bar: certificate?.show_in_trust_bar ?? false,
    is_active: certificate?.is_active ?? true,
});

export default function CertificateForm({ certificate }: Props) {
    const isEdit = certificate !== null;
    const { data, setData, post, put, processing, errors } =
        useForm<CertificateFormData>(toForm(certificate));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateCertificate(certificate.id).url);

            return;
        }

        post(storeCertificate().url);
    };

    const nameError = errors[`name.${LOCALE}` as keyof typeof errors];
    const descriptionError =
        errors[`description.${LOCALE}` as keyof typeof errors];

    const currentLogo =
        data.remove_logo || data.logo_token
            ? null
            : (certificate?.logo_url ?? null);

    const currentFile =
        data.remove_file || data.file_token
            ? null
            : (certificate?.file_url ?? null);

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Identitas Sertifikat"
                description="Nama dan penerbit seperti tertulis pada dokumen aslinya."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="name">
                            Nama sertifikat
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="name"
                            value={data.name[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('name', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Contoh: ISO 9001:2015"
                            aria-invalid={Boolean(nameError)}
                        />
                        {nameError && (
                            <p className="text-xs text-destructive">
                                {nameError}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="issuer">Penerbit</Label>
                        <Input
                            id="issuer"
                            value={data.issuer}
                            onChange={(event) =>
                                setData('issuer', event.target.value)
                            }
                            placeholder="Contoh: TUV Rheinland"
                            aria-invalid={Boolean(errors.issuer)}
                        />
                        {errors.issuer && (
                            <p className="text-xs text-destructive">
                                {errors.issuer}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="certificate_number">
                            Nomor sertifikat
                        </Label>
                        <Input
                            id="certificate_number"
                            dir="ltr"
                            value={data.certificate_number}
                            onChange={(event) =>
                                setData(
                                    'certificate_number',
                                    event.target.value,
                                )
                            }
                            placeholder="Contoh: 01 100 1234567"
                            aria-invalid={Boolean(errors.certificate_number)}
                        />
                        {errors.certificate_number && (
                            <p className="text-xs text-destructive">
                                {errors.certificate_number}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5 sm:col-span-2">
                        <Label htmlFor="description">Deskripsi</Label>
                        <Textarea
                            id="description"
                            rows={3}
                            value={data.description[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('description', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Ruang lingkup sertifikat dan apa yang dijaminnya."
                            aria-invalid={Boolean(descriptionError)}
                        />
                        {descriptionError && (
                            <p className="text-xs text-destructive">
                                {descriptionError}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Masa Berlaku"
                description="Sertifikat yang kedaluwarsa dalam 60 hari akan diingatkan di dashboard."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="issued_at">Tanggal terbit</Label>
                        <DatePicker
                            id="issued_at"
                            value={data.issued_at}
                            onChange={(value) => setData('issued_at', value)}
                            placeholder="Pilih tanggal terbit"
                            aria-invalid={Boolean(errors.issued_at)}
                        />
                        {errors.issued_at && (
                            <p className="text-xs text-destructive">
                                {errors.issued_at}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="expires_at">Tanggal kedaluwarsa</Label>
                        <DatePicker
                            id="expires_at"
                            value={data.expires_at}
                            onChange={(value) => setData('expires_at', value)}
                            placeholder="Kosongkan bila tidak kedaluwarsa"
                            aria-invalid={Boolean(errors.expires_at)}
                        />
                        {errors.expires_at && (
                            <p className="text-xs text-destructive">
                                {errors.expires_at}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Berkas"
                description="Logo penerbit tampil di trust bar; hasil scan dipakai buyer untuk verifikasi."
            >
                <div className="grid gap-6 md:grid-cols-2">
                    <div className="grid gap-2">
                        <Label>Logo penerbit</Label>

                        {currentLogo && (
                            <div className="flex items-center gap-3">
                                <img
                                    src={currentLogo}
                                    alt="Logo penerbit saat ini"
                                    className="size-16 rounded-[var(--radius)] border border-border object-contain p-1.5"
                                />
                                <Button
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    onClick={() => setData('remove_logo', true)}
                                >
                                    <Trash2 />
                                    Hapus Logo
                                </Button>
                            </div>
                        )}

                        <ImageUploader
                            kind="logo"
                            onChange={(token) => setData('logo_token', token)}
                            hint="PNG, JPG, WEBP, atau SVG. Unggah ulang untuk mengganti logo lama."
                        />
                        {errors.logo_token && (
                            <p className="text-xs text-destructive">
                                {errors.logo_token}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label>Berkas scan sertifikat</Label>

                        {currentFile && (
                            <div className="flex items-center gap-2">
                                <Button asChild variant="detail" size="sm">
                                    <a
                                        href={currentFile}
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        <ExternalLink />
                                        Lihat Berkas
                                    </a>
                                </Button>
                                <Button
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    onClick={() => setData('remove_file', true)}
                                >
                                    <Trash2 />
                                    Hapus Berkas
                                </Button>
                            </div>
                        )}

                        <DocumentUploader
                            onChange={(tokens) =>
                                setData('file_token', tokens.at(-1) ?? null)
                            }
                        />
                        {errors.file_token && (
                            <p className="text-xs text-destructive">
                                {errors.file_token}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel title="Tampilan & Status">
                <div className="grid gap-3">
                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="show_in_trust_bar">
                                Tampilkan di trust bar
                            </Label>
                            <p className="text-xs text-muted-foreground">
                                Sertifikat ini muncul di deretan logo pada
                                beranda dan halaman produk.
                            </p>
                        </div>
                        <Switch
                            id="show_in_trust_bar"
                            checked={data.show_in_trust_bar}
                            onCheckedChange={(checked) =>
                                setData('show_in_trust_bar', checked)
                            }
                        />
                    </div>

                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="is_active">Aktif</Label>
                            <p className="text-xs text-muted-foreground">
                                Sertifikat nonaktif tidak tampil di halaman
                                publik mana pun.
                            </p>
                        </div>
                        <Switch
                            id="is_active"
                            checked={data.is_active}
                            onCheckedChange={(checked) =>
                                setData('is_active', checked)
                            }
                        />
                    </div>
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={certificatesIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-certificate-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan Sertifikat'}
                </Button>
            </div>
        </form>
    );
}
