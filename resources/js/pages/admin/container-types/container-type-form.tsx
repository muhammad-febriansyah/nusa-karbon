import { Link, useForm } from '@inertiajs/react';
import { Info, Save, X } from 'lucide-react';
import InputDecimal from '@/components/form/input-decimal';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import {
    store as storeContainerType,
    update as updateContainerType,
    index as containerTypesIndex,
} from '@/routes/admin/container-types';
import type { ContainerType, Translated } from '@/types/models';

const LOCALE = 'id';

type Props = {
    containerType: ContainerType | null;
};

type ContainerTypeForm = {
    code: string;
    name: Translated;
    max_payload_kg: number | null;
    max_cbm: number | null;
    internal_length_m: number | null;
    internal_width_m: number | null;
    internal_height_m: number | null;
    is_active: boolean;
};

const toForm = (containerType: ContainerType | null): ContainerTypeForm => ({
    code: containerType?.code ?? '',
    name: { [LOCALE]: containerType?.name?.[LOCALE] ?? '' },
    max_payload_kg: containerType?.max_payload_kg ?? null,
    max_cbm: containerType?.max_cbm ?? null,
    internal_length_m: containerType?.internal_length_m ?? null,
    internal_width_m: containerType?.internal_width_m ?? null,
    internal_height_m: containerType?.internal_height_m ?? null,
    is_active: containerType?.is_active ?? true,
});

const round2 = (value: number) => Math.round(value * 100) / 100;

export default function ContainerTypeForm({ containerType }: Props) {
    const isEdit = containerType !== null;
    const { data, setData, post, put, processing, errors } =
        useForm<ContainerTypeForm>(toForm(containerType));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateContainerType(containerType.id).url);

            return;
        }

        post(storeContainerType().url);
    };

    const nameError = errors[`name.${LOCALE}` as keyof typeof errors];

    // Volume geometris hanya sebagai pembanding: kapasitas muat praktis
    // selalu lebih kecil karena karton tidak bisa mengisi ruang sempurna.
    const geometricVolume =
        data.internal_length_m &&
        data.internal_width_m &&
        data.internal_height_m
            ? round2(
                  data.internal_length_m *
                      data.internal_width_m *
                      data.internal_height_m,
              )
            : null;

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Identitas"
                description="Kode dan nama kontainer seperti dipakai di dokumen pengiriman."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="code">
                            Kode kontainer
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="code"
                            dir="ltr"
                            value={data.code}
                            onChange={(event) =>
                                setData('code', event.target.value)
                            }
                            placeholder="Contoh: 20FT_DV"
                            aria-invalid={Boolean(errors.code)}
                        />
                        {errors.code && (
                            <p className="text-xs text-destructive">
                                {errors.code}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="name">
                            Nama kontainer
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
                            placeholder="Contoh: 20FT Dry Van"
                            aria-invalid={Boolean(nameError)}
                        />
                        {nameError && (
                            <p className="text-xs text-destructive">
                                {nameError}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Kapasitas Muat"
                description="Angka yang dipakai Container Calculator untuk menghitung jumlah master carton."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="max_payload_kg">
                            Kapasitas berat
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <InputDecimal
                            id="max_payload_kg"
                            unit="kg"
                            precision={2}
                            value={data.max_payload_kg}
                            onChange={(value) =>
                                setData('max_payload_kg', value)
                            }
                            placeholder="Contoh: 18000"
                            aria-invalid={Boolean(errors.max_payload_kg)}
                        />
                        {errors.max_payload_kg && (
                            <p className="text-xs text-destructive">
                                {errors.max_payload_kg}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="max_cbm">
                            Kapasitas volume
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <InputDecimal
                            id="max_cbm"
                            unit="CBM"
                            precision={2}
                            value={data.max_cbm}
                            onChange={(value) => setData('max_cbm', value)}
                            placeholder="Contoh: 28"
                            aria-invalid={Boolean(errors.max_cbm)}
                        />
                        {errors.max_cbm && (
                            <p className="text-xs text-destructive">
                                {errors.max_cbm}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Dimensi Internal"
                description="Opsional, tampil di halaman publik sebagai informasi tambahan."
            >
                <div className="grid gap-4 sm:grid-cols-3">
                    <div className="grid gap-1.5">
                        <Label htmlFor="internal_length_m">Panjang</Label>
                        <InputDecimal
                            id="internal_length_m"
                            unit="m"
                            precision={2}
                            value={data.internal_length_m}
                            onChange={(value) =>
                                setData('internal_length_m', value)
                            }
                            placeholder="Contoh: 5,90"
                            aria-invalid={Boolean(errors.internal_length_m)}
                        />
                        {errors.internal_length_m && (
                            <p className="text-xs text-destructive">
                                {errors.internal_length_m}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="internal_width_m">Lebar</Label>
                        <InputDecimal
                            id="internal_width_m"
                            unit="m"
                            precision={2}
                            value={data.internal_width_m}
                            onChange={(value) =>
                                setData('internal_width_m', value)
                            }
                            placeholder="Contoh: 2,35"
                            aria-invalid={Boolean(errors.internal_width_m)}
                        />
                        {errors.internal_width_m && (
                            <p className="text-xs text-destructive">
                                {errors.internal_width_m}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="internal_height_m">Tinggi</Label>
                        <InputDecimal
                            id="internal_height_m"
                            unit="m"
                            precision={2}
                            value={data.internal_height_m}
                            onChange={(value) =>
                                setData('internal_height_m', value)
                            }
                            placeholder="Contoh: 2,39"
                            aria-invalid={Boolean(errors.internal_height_m)}
                        />
                        {errors.internal_height_m && (
                            <p className="text-xs text-destructive">
                                {errors.internal_height_m}
                            </p>
                        )}
                    </div>
                </div>

                {geometricVolume !== null && (
                    <div className="mt-4 flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-info/25 border-s-info bg-info/10 px-3.5 py-2.5">
                        <Info
                            aria-hidden
                            className="mt-0.5 size-4 shrink-0 text-info"
                        />
                        <p className="text-[13px] text-info">
                            Volume geometris dari dimensi di atas{' '}
                            <span dir="ltr" className="tabular-nums">
                                {geometricVolume.toLocaleString('id-ID')} CBM
                            </span>
                            . Kapasitas muat wajar lebih kecil dari angka ini —
                            karton tidak mengisi ruang secara sempurna.
                        </p>
                    </div>
                )}
            </Panel>

            <Panel title="Status">
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <div className="space-y-0.5">
                        <Label htmlFor="is_active">Aktif</Label>
                        <p className="text-xs text-muted-foreground">
                            Kontainer nonaktif tidak muncul di Container
                            Calculator maupun form RFQ.
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
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={containerTypesIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-container-type-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan Tipe Kontainer'}
                </Button>
            </div>
        </form>
    );
}
