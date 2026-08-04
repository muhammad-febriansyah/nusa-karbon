import { useForm } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import { useEffect, useState } from 'react';
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
import {
    store as storeCategory,
    update as updateCategory,
} from '@/routes/admin/product-categories';
import type { ProductCategory, Translated } from '@/types/models';

/**
 * Locale konten aktif. Sementara hanya Bahasa Indonesia; bahasa lain
 * dihidupkan di Fase 6 lewat `config/locales.php`.
 */
const LOCALE = 'id';

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    /** Null saat menambah, terisi saat mengubah. */
    category: ProductCategory | null;
};

type CategoryForm = {
    name: Translated;
    slug: Translated;
    description: Translated;
    icon: string;
    is_active: boolean;
};

const toForm = (category: ProductCategory | null): CategoryForm => ({
    name: { [LOCALE]: category?.name?.[LOCALE] ?? '' },
    slug: { [LOCALE]: category?.slug?.[LOCALE] ?? '' },
    description: { [LOCALE]: category?.description?.[LOCALE] ?? '' },
    icon: category?.icon ?? '',
    is_active: category?.is_active ?? true,
});

/**
 * Form kategori memakai dialog atas permintaan klien: field-nya sedikit dan
 * seluruhnya muat tanpa menggulir. Menyimpang dari docs/07-admin-ui-standards.md
 * bagian 2 yang mewajibkan halaman terpisah untuk CRUD, jadi dialog ini dikunci
 * agar tidak tertutup tanpa sengaja selama ada isian.
 */
export default function CategoryFormDialog({
    open,
    onOpenChange,
    category,
}: Props) {
    const isEdit = category !== null;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm<CategoryForm>(toForm(category));

    const [confirmCloseOpen, setConfirmCloseOpen] = useState(false);

    // Mengisi ulang saat dialog dibuka untuk kategori yang berbeda.
    useEffect(() => {
        if (open) {
            reset();
            clearErrors();
            setData(toForm(category));
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, category?.id]);

    const isDirty = JSON.stringify(data) !== JSON.stringify(toForm(category));

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
            put(updateCategory(category.id).url, options);

            return;
        }

        post(storeCategory().url, options);
    };

    const nameError = errors[`name.${LOCALE}` as keyof typeof errors];
    const slugError = errors[`slug.${LOCALE}` as keyof typeof errors];
    const descriptionError =
        errors[`description.${LOCALE}` as keyof typeof errors];

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
                            ? `Ubah kategori "${category.name?.[LOCALE] ?? ''}"`
                            : 'Tambah Kategori Produk'}
                    </DialogTitle>
                    <DialogDescription>
                        Kategori dipakai untuk mengelompokkan produk di website
                        publik.
                    </DialogDescription>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-1.5">
                            <Label htmlFor="category-name">
                                Nama kategori
                                <span aria-hidden className="text-destructive">
                                    *
                                </span>
                            </Label>
                            <Input
                                id="category-name"
                                value={data.name[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('name', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: Arang Shisha"
                                aria-invalid={Boolean(nameError)}
                            />
                            {nameError && (
                                <p className="text-xs text-destructive">
                                    {nameError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="category-slug">Slug URL</Label>
                            <Input
                                id="category-slug"
                                dir="ltr"
                                value={data.slug[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('slug', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: arang-shisha — kosongkan untuk dibuat otomatis dari nama"
                                aria-invalid={Boolean(slugError)}
                            />
                            {slugError && (
                                <p className="text-xs text-destructive">
                                    {slugError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="category-description">
                                Deskripsi singkat
                            </Label>
                            <Textarea
                                id="category-description"
                                rows={3}
                                value={data.description[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('description', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Contoh: Briket arang batok kelapa untuk shisha, bakar bersih dan tahan lama."
                                aria-invalid={Boolean(descriptionError)}
                            />
                            {descriptionError && (
                                <p className="text-xs text-destructive">
                                    {descriptionError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="category-icon">Nama ikon</Label>
                            <Input
                                id="category-icon"
                                dir="ltr"
                                value={data.icon}
                                onChange={(event) =>
                                    setData('icon', event.target.value)
                                }
                                placeholder="Contoh: flame — nama ikon dari lucide.dev, boleh dikosongkan"
                                aria-invalid={Boolean(errors.icon)}
                            />
                            {errors.icon && (
                                <p className="text-xs text-destructive">
                                    {errors.icon}
                                </p>
                            )}
                        </div>

                        <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                            <div className="space-y-0.5">
                                <Label htmlFor="is_active">Aktif</Label>
                                <p className="text-xs text-muted-foreground">
                                    Kategori nonaktif tidak tampil di website
                                    publik.
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
                                data-test="save-category-button"
                            >
                                {processing ? <Spinner /> : <Save />}
                                {processing
                                    ? 'Menyimpan...'
                                    : isEdit
                                      ? 'Simpan Perubahan'
                                      : 'Simpan Kategori'}
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
