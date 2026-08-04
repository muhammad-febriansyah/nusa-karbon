import { useForm } from '@inertiajs/react';
import { Save, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputDecimal from '@/components/form/input-decimal';
import ConfirmDialog from '@/components/shared/confirm-dialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import {
    store as storePackagingOption,
    update as updatePackagingOption,
} from '@/routes/admin/packaging-options';
import type { PackagingOption, Translated } from '@/types/models';

const LOCALE = 'id';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Null saat menambah, terisi saat mengubah. */
    option: PackagingOption | null;
};

type PackagingForm = {
    name: Translated;
    description: Translated;
    net_weight_kg: number | null;
    image_token: string | null;
    remove_image: boolean;
    is_active: boolean;
};

const toForm = (option: PackagingOption | null): PackagingForm => ({
    name: { [LOCALE]: option?.name?.[LOCALE] ?? '' },
    description: { [LOCALE]: option?.description?.[LOCALE] ?? '' },
    net_weight_kg: option?.net_weight_kg ?? null,
    image_token: null,
    remove_image: false,
    is_active: option?.is_active ?? true,
});

/**
 * Form kemasan memakai dialog atas permintaan klien: lima field dan seluruhnya
 * muat tanpa menggulir. Menyimpang dari docs/07-admin-ui-standards.md bagian 2
 * yang mewajibkan halaman terpisah, jadi dialog ini dikunci agar tidak tertutup
 * tanpa sengaja selama ada isian.
 */
export default function PackagingFormDialog({
    open,
    onOpenChange,
    option,
}: Props) {
    const isEdit = option !== null;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<PackagingForm>(toForm(option));

    const [confirmCloseOpen, setConfirmCloseOpen] = useState(false);

    // Mengisi ulang saat dialog dibuka untuk kemasan yang berbeda.
    useEffect(() => {
        if (open) {
            reset();
            clearErrors();
            setData(toForm(option));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, option?.id]);

    const isDirty = JSON.stringify(data) !== JSON.stringify(toForm(option));

    const requestClose = () => {
        if (isDirty) {
            setConfirmCloseOpen(true);

            return;
        }

        onOpenChange(false);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        };

        if (isEdit) {
            put(updatePackagingOption(option.id).url, options);

            return;
        }

        post(storePackagingOption().url, options);
    };

    const nameError = errors[`name.${LOCALE}` as keyof typeof errors];
    const descriptionError =
        errors[`description.${LOCALE}` as keyof typeof errors];

    const currentImage =
        data.remove_image || data.image_token
            ? null
            : (option?.image_url ?? null);

    return (
        <>
            <Dialog
                open={open}
                onOpenChange={(next) => {
                    if (!next) {
                        requestClose();

                        return;
                    }

                    onOpenChange(true);
                }}
            >
                <DialogContent className="sm:max-w-xl">
                    <DialogTitle>
                        {isEdit
                            ? `Ubah kemasan "${option.name?.[LOCALE] ?? ''}"`
                            : 'Tambah Opsi Kemasan'}
                    </DialogTitle>
                    <DialogDescription>
                        Opsi kemasan tampil di halaman produk dan bisa dipilih
                        buyer saat mengirim RFQ.
                    </DialogDescription>

                    <form
                        onSubmit={submit}
                        className="scrollbar-slim max-h-[65vh] space-y-4 overflow-y-auto"
                    >
                        <div className="grid gap-1.5">
                            <Label htmlFor="packaging-name">
                                Nama kemasan
                                <span aria-hidden className="text-destructive">
                                    *
                                </span>
                            </Label>
                            <Input
                                id="packaging-name"
                                value={data.name[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('name', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: Inner Box 1 kg"
                                aria-invalid={Boolean(nameError)}
                            />
                            {nameError && (
                                <p className="text-xs text-destructive">
                                    {nameError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="packaging-description">
                                Deskripsi singkat
                            </Label>
                            <Textarea
                                id="packaging-description"
                                rows={3}
                                value={data.description[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('description', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: Kotak karton isi 1 kg, dibungkus plastik segel."
                                aria-invalid={Boolean(descriptionError)}
                            />
                            {descriptionError && (
                                <p className="text-xs text-destructive">
                                    {descriptionError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="packaging-net-weight">
                                Berat bersih
                            </Label>
                            <InputDecimal
                                id="packaging-net-weight"
                                unit="kg"
                                precision={3}
                                value={data.net_weight_kg}
                                onChange={(value) =>
                                    setData('net_weight_kg', value)
                                }
                                placeholder="Contoh: 1 — kosongkan untuk kemasan custom"
                                aria-invalid={Boolean(errors.net_weight_kg)}
                            />
                            {errors.net_weight_kg && (
                                <p className="text-xs text-destructive">
                                    {errors.net_weight_kg}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label>Gambar kemasan</Label>
                            {currentImage && (
                                <div className="flex items-center gap-3">
                                    <img
                                        src={currentImage}
                                        alt="Gambar kemasan saat ini"
                                        className="size-20 rounded-[var(--radius)] border border-border object-cover"
                                    />
                                    <Button
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        onClick={() =>
                                            setData('remove_image', true)
                                        }
                                    >
                                        <Trash2 />
                                        Hapus Gambar
                                    </Button>
                                </div>
                            )}
                            <ImageUploader
                                kind="gallery"
                                onChange={(token) =>
                                    setData('image_token', token)
                                }
                                hint="Unggah ulang untuk mengganti gambar lama."
                            />
                        </div>

                        <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                            <div className="space-y-0.5">
                                <Label htmlFor="is_active">Aktif</Label>
                                <p className="text-xs text-muted-foreground">
                                    Kemasan nonaktif tidak bisa dipilih di form
                                    produk maupun RFQ.
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

                        <DialogFooter className="gap-2">
                            <Button
                                type="button"
                                variant="cancel"
                                onClick={requestClose}
                                disabled={processing}
                            >
                                <X />
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                variant="save"
                                disabled={processing}
                                data-test="save-packaging-button"
                            >
                                {processing ? <Spinner /> : <Save />}
                                {processing
                                    ? 'Menyimpan...'
                                    : isEdit
                                      ? 'Simpan Perubahan'
                                      : 'Simpan Kemasan'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={confirmCloseOpen}
                onOpenChange={setConfirmCloseOpen}
                tone="warning"
                title="Tutup tanpa menyimpan?"
                description="Perubahan yang belum disimpan akan hilang."
                confirmLabel="Tutup Tanpa Menyimpan"
                confirmVariant="archive"
                onConfirm={() => {
                    setConfirmCloseOpen(false);
                    onOpenChange(false);
                }}
            />
        </>
    );
}
