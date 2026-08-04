import { Link, useForm } from '@inertiajs/react';
import { Save, ShieldAlert, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import DatePicker from '@/components/form/date-picker';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import ImageUploader from '@/components/upload/image-uploader';
import {
    index as representativesIndex,
    store as storeRepresentative,
    update as updateRepresentative,
} from '@/routes/admin/representatives';
import type { Representative, Translated } from '@/types/models';

const LOCALE = 'id';

const REGIONS = ['MENA', 'Europe', 'Asia', 'Americas', 'Africa'];

type Props = {
    representative: Representative | null;
};

type RepresentativeFormData = {
    name: string;
    position: Translated;
    region: string;
    email: string;
    phone: string;
    whatsapp: string;
    started_at: string | null;
    ended_at: string | null;
    photo_token: string | null;
    remove_photo: boolean;
    is_active: boolean;
};

const toForm = (
    representative: Representative | null,
): RepresentativeFormData => ({
    name: representative?.name ?? '',
    position: { [LOCALE]: representative?.position?.[LOCALE] ?? '' },
    region: representative?.region ?? '',
    email: representative?.email ?? '',
    phone: representative?.phone ?? '',
    whatsapp: representative?.whatsapp ?? '',
    started_at: representative?.started_at ?? null,
    ended_at: representative?.ended_at ?? null,
    photo_token: null,
    remove_photo: false,
    is_active: representative?.is_active ?? true,
});

export default function RepresentativeForm({ representative }: Props) {
    const isEdit = representative !== null;
    const [confirmingDeactivate, setConfirmingDeactivate] = useState(false);
    const { data, setData, post, put, processing, errors } =
        useForm<RepresentativeFormData>(toForm(representative));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateRepresentative(representative.id).url);

            return;
        }

        post(storeRepresentative().url);
    };

    const positionError = errors[`position.${LOCALE}` as keyof typeof errors];

    const currentPhoto =
        data.remove_photo || data.photo_token
            ? null
            : (representative?.photo_url ?? null);

    /**
     * Menonaktifkan perwakilan menghapusnya dari halaman verifikasi publik,
     * jadi admin dimintai konfirmasi lebih dulu — bukan hanya sekali klik.
     */
    const handleActiveChange = (checked: boolean) => {
        if (!checked && isEdit && representative.is_active) {
            setConfirmingDeactivate(true);

            return;
        }

        setData('is_active', checked);
    };

    return (
        <>
            <form onSubmit={submit} className="space-y-5">
                <Panel
                    title="Identitas"
                    description="Nama dan jabatan seperti yang dikenal buyer saat dihubungi."
                >
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-1.5">
                            <Label htmlFor="name">
                                Nama lengkap
                                <span aria-hidden className="text-destructive">
                                    *
                                </span>
                            </Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                placeholder="Contoh: Adi Wicaksono"
                                aria-invalid={Boolean(errors.name)}
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="position">
                                Jabatan
                                <span aria-hidden className="text-destructive">
                                    *
                                </span>
                            </Label>
                            <Input
                                id="position"
                                value={data.position[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('position', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: Export Sales Manager"
                                aria-invalid={Boolean(positionError)}
                            />
                            {positionError && (
                                <p className="text-xs text-destructive">
                                    {positionError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="region">Wilayah</Label>
                            <Select
                                value={
                                    data.region === '' ? 'none' : data.region
                                }
                                onValueChange={(value) =>
                                    setData(
                                        'region',
                                        value === 'none' ? '' : value,
                                    )
                                }
                            >
                                <SelectTrigger id="region">
                                    <SelectValue placeholder="Pilih wilayah" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="none">
                                        Tanpa wilayah
                                    </SelectItem>
                                    {REGIONS.map((region) => (
                                        <SelectItem key={region} value={region}>
                                            {region}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.region && (
                                <p className="text-xs text-destructive">
                                    {errors.region}
                                </p>
                            )}
                        </div>
                    </div>
                </Panel>

                <Panel
                    title="Kontak Resmi"
                    description="Hanya email domain perusahaan yang diterima — inilah yang dicek buyer saat memverifikasi."
                >
                    <div className="mb-4 flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-warning/25 border-s-warning bg-warning/10 px-3.5 py-2.5">
                        <ShieldAlert
                            aria-hidden
                            className="mt-0.5 size-4 shrink-0 text-warning"
                        />
                        <p className="text-[13px] text-warning">
                            Email gratisan seperti Gmail atau Yahoo ditolak.
                            Penipu memakai alamat semacam itu untuk menyamar
                            sebagai perwakilan resmi.
                        </p>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-3">
                        <div className="grid gap-1.5">
                            <Label htmlFor="email">
                                Email perusahaan
                                <span aria-hidden className="text-destructive">
                                    *
                                </span>
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                dir="ltr"
                                value={data.email}
                                onChange={(event) =>
                                    setData('email', event.target.value)
                                }
                                placeholder="nama@nusacarbon.com"
                                aria-invalid={Boolean(errors.email)}
                            />
                            {errors.email && (
                                <p className="text-xs text-destructive">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="phone">Telepon</Label>
                            <Input
                                id="phone"
                                dir="ltr"
                                value={data.phone}
                                onChange={(event) =>
                                    setData('phone', event.target.value)
                                }
                                placeholder="+62 21 1234 5678"
                                aria-invalid={Boolean(errors.phone)}
                            />
                            {errors.phone && (
                                <p className="text-xs text-destructive">
                                    {errors.phone}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="whatsapp">WhatsApp</Label>
                            <Input
                                id="whatsapp"
                                dir="ltr"
                                value={data.whatsapp}
                                onChange={(event) =>
                                    setData('whatsapp', event.target.value)
                                }
                                placeholder="+62 812 3456 7890"
                                aria-invalid={Boolean(errors.whatsapp)}
                            />
                            {errors.whatsapp && (
                                <p className="text-xs text-destructive">
                                    {errors.whatsapp}
                                </p>
                            )}
                        </div>
                    </div>
                </Panel>

                <Panel
                    title="Masa Tugas & Foto"
                    description="Tanggal berakhir diisi saat perwakilan tidak lagi mewakili perusahaan."
                >
                    <div className="grid gap-6 md:grid-cols-2">
                        <div className="grid gap-4">
                            <div className="grid gap-1.5">
                                <Label htmlFor="started_at">
                                    Tanggal mulai
                                </Label>
                                <DatePicker
                                    id="started_at"
                                    value={data.started_at}
                                    onChange={(value) =>
                                        setData('started_at', value)
                                    }
                                    placeholder="Pilih tanggal mulai"
                                    aria-invalid={Boolean(errors.started_at)}
                                />
                                {errors.started_at && (
                                    <p className="text-xs text-destructive">
                                        {errors.started_at}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-1.5">
                                <Label htmlFor="ended_at">
                                    Tanggal berakhir
                                </Label>
                                <DatePicker
                                    id="ended_at"
                                    value={data.ended_at}
                                    onChange={(value) =>
                                        setData('ended_at', value)
                                    }
                                    placeholder="Kosongkan bila masih aktif"
                                    aria-invalid={Boolean(errors.ended_at)}
                                />
                                {errors.ended_at && (
                                    <p className="text-xs text-destructive">
                                        {errors.ended_at}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="grid gap-2">
                            <Label>Foto perwakilan</Label>

                            {currentPhoto && (
                                <div className="flex items-center gap-3">
                                    <img
                                        src={currentPhoto}
                                        alt="Foto perwakilan saat ini"
                                        className="size-16 rounded-full border border-border object-cover"
                                    />
                                    <Button
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        onClick={() =>
                                            setData('remove_photo', true)
                                        }
                                    >
                                        <Trash2 />
                                        Hapus Foto
                                    </Button>
                                </div>
                            )}

                            <ImageUploader
                                kind="avatar"
                                onChange={(token) =>
                                    setData('photo_token', token)
                                }
                                hint="Foto wajah jelas, minimal 400 × 400 piksel."
                            />
                            {errors.photo_token && (
                                <p className="text-xs text-destructive">
                                    {errors.photo_token}
                                </p>
                            )}
                        </div>
                    </div>
                </Panel>

                <Panel title="Status">
                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="is_active">Aktif</Label>
                            <p className="text-xs text-muted-foreground">
                                Perwakilan nonaktif hilang dari halaman
                                verifikasi publik.
                            </p>
                        </div>
                        <Switch
                            id="is_active"
                            checked={data.is_active}
                            onCheckedChange={handleActiveChange}
                        />
                    </div>
                </Panel>

                <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                    <Button asChild variant="cancel" type="button">
                        <Link href={representativesIndex()}>
                            <X />
                            Batal
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        variant="save"
                        disabled={processing}
                        data-test="save-representative-button"
                    >
                        {processing ? <Spinner /> : <Save />}
                        {processing
                            ? 'Menyimpan...'
                            : isEdit
                              ? 'Simpan Perubahan'
                              : 'Simpan Perwakilan'}
                    </Button>
                </div>
            </form>

            <ConfirmDialog
                open={confirmingDeactivate}
                onOpenChange={setConfirmingDeactivate}
                tone="warning"
                confirmVariant="archive"
                title={`Nonaktifkan ${data.name || 'perwakilan ini'}?`}
                description="Perwakilan langsung hilang dari halaman Verify Our Representative. Buyer yang mengecek namanya akan mendapati orang ini bukan perwakilan resmi — pastikan itu memang yang dimaksud."
                confirmLabel="Nonaktifkan Perwakilan"
                onConfirm={() => {
                    setData('is_active', false);
                    setConfirmingDeactivate(false);
                }}
            />
        </>
    );
}
