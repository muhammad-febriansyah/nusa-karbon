import { Link, useForm } from '@inertiajs/react';
import { Save, Trash2, X } from 'lucide-react';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import {
    index as galleriesIndex,
    store as storeGallery,
    update as updateGallery,
} from '@/routes/admin/galleries';
import type { Gallery, Translated } from '@/types/models';

const LOCALE = 'id';

const slugify = (value: string) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

type Props = {
    gallery: Gallery | null;
};

type GalleryFormData = {
    title: Translated;
    slug: Translated;
    description: Translated;
    cover_token: string | null;
    remove_cover: boolean;
    is_active: boolean;
};

export default function GalleryForm({ gallery }: Props) {
    const isEdit = gallery !== null;
    const { data, setData, post, put, processing, errors } =
        useForm<GalleryFormData>({
            title: { [LOCALE]: gallery?.title?.[LOCALE] ?? '' },
            slug: { [LOCALE]: gallery?.slug?.[LOCALE] ?? '' },
            description: { [LOCALE]: gallery?.description?.[LOCALE] ?? '' },
            cover_token: null,
            remove_cover: false,
            is_active: gallery?.is_active ?? true,
        });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateGallery(gallery.id).url);

            return;
        }

        post(storeGallery().url);
    };

    const titleError = errors[`title.${LOCALE}` as keyof typeof errors];
    const slugError = errors[`slug.${LOCALE}` as keyof typeof errors];
    const descriptionError =
        errors[`description.${LOCALE}` as keyof typeof errors];

    const currentCover =
        data.remove_cover || data.cover_token
            ? null
            : (gallery?.cover_url ?? null);

    /**
     * Slug ikut judul selama admin belum menyuntingnya sendiri. Pada album
     * yang sudah tersimpan slug dibiarkan — mengubahnya memutus tautan lama.
     */
    const handleTitleChange = (value: string) => {
        setData((current) => ({
            ...current,
            title: { [LOCALE]: value },
            slug:
                isEdit ||
                current.slug[LOCALE] !== slugify(current.title[LOCALE] ?? '')
                    ? current.slug
                    : { [LOCALE]: slugify(value) },
        }));
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Identitas Album"
                description="Judul dan slug yang dipakai di alamat halaman publik."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="title">
                            Judul album
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="title"
                            value={data.title[LOCALE] ?? ''}
                            onChange={(event) =>
                                handleTitleChange(event.target.value)
                            }
                            placeholder="Contoh: Proses Stuffing Kontainer"
                            aria-invalid={Boolean(titleError)}
                        />
                        {titleError && (
                            <p className="text-xs text-destructive">
                                {titleError}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="slug">
                            Slug
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="slug"
                            dir="ltr"
                            value={data.slug[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('slug', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="proses-stuffing-kontainer"
                            aria-invalid={Boolean(slugError)}
                        />
                        {slugError && (
                            <p className="text-xs text-destructive">
                                {slugError}
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
                            placeholder="Apa yang ditunjukkan album ini."
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
                title="Sampul"
                description="Gambar yang mewakili album di daftar galeri."
            >
                <div className="grid gap-2">
                    {currentCover && (
                        <div className="flex items-center gap-3">
                            <img
                                src={currentCover}
                                alt="Sampul album saat ini"
                                className="h-20 rounded-[var(--radius)] border border-border object-cover"
                            />
                            <Button
                                type="button"
                                variant="delete"
                                size="sm"
                                onClick={() => setData('remove_cover', true)}
                            >
                                <Trash2 />
                                Hapus Sampul
                            </Button>
                        </div>
                    )}

                    <ImageUploader
                        kind="gallery"
                        onChange={(token) => setData('cover_token', token)}
                    />
                </div>
            </Panel>

            <Panel title="Status">
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <div className="space-y-0.5">
                        <Label htmlFor="is_active">Aktif</Label>
                        <p className="text-xs text-muted-foreground">
                            Album nonaktif beserta isinya tidak tampil di
                            halaman publik.
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
                    <Link href={galleriesIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-gallery-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan & Isi Album'}
                </Button>
            </div>
        </form>
    );
}
