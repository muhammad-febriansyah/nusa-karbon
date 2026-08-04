import { Link, useForm } from '@inertiajs/react';
import { Film, Plus, Save, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import SortableList from '@/components/shared/sortable-list';
import StatusBadge from '@/components/shared/status-badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import MultiImageUploader from '@/components/upload/multi-image-uploader';
import { dashboard } from '@/routes/admin';
import { index as galleriesIndex } from '@/routes/admin/galleries';
import {
    edit as galleryItemsEdit,
    sync as syncGalleryItems,
} from '@/routes/admin/galleries/items';
import type { Gallery, GalleryAlbumItem, Translated } from '@/types/models';

const LOCALE = 'id';

type Props = {
    gallery: Gallery;
};

/** Baris di dalam form; `key` lokal supaya drag-and-drop tetap stabil. */
type ItemRow = {
    key: string;
    id: number | null;
    type: 'image' | 'video';
    caption: Translated;
    video_url: string;
    /** Token unggahan baru; null pada baris yang berkasnya sudah tersimpan. */
    token: string | null;
    previewUrl: string | null;
};

type ItemsFormData = {
    items: ItemRow[];
};

const toRow = (item: GalleryAlbumItem): ItemRow => ({
    key: `saved-${item.id}`,
    id: item.id,
    type: item.type,
    caption: { [LOCALE]: item.caption?.[LOCALE] ?? '' },
    video_url: item.video_url ?? '',
    token: null,
    previewUrl: item.file_url,
});

export default function GalleryItems({ gallery }: Props) {
    const [videoUrl, setVideoUrl] = useState('');
    const { data, setData, transform, put, processing, errors } =
        useForm<ItemsFormData>({
            items: (gallery.items ?? []).map(toRow),
        });

    // Server hanya butuh data itemnya, bukan kunci lokal dan pratinjau.
    transform((payload) => ({
        items: payload.items.map((item) => ({
            id: item.id,
            type: item.type,
            caption: item.caption,
            video_url: item.type === 'video' ? item.video_url : null,
            token: item.token,
        })),
    }));

    const label = gallery.title?.[LOCALE] ?? 'Album';

    const addImages = (tokens: string[]) => {
        const existing = new Set(
            data.items.map((item) => item.token).filter(Boolean),
        );
        const fresh = tokens.filter((token) => !existing.has(token));

        if (fresh.length === 0) {
            return;
        }

        setData('items', [
            ...data.items,
            ...fresh.map((token) => ({
                key: `upload-${token}`,
                id: null,
                type: 'image' as const,
                caption: { [LOCALE]: '' },
                video_url: '',
                token,
                previewUrl: null,
            })),
        ]);
    };

    const addVideo = () => {
        if (videoUrl.trim() === '') {
            return;
        }

        setData('items', [
            ...data.items,
            {
                key: `video-${data.items.length}-${videoUrl}`,
                id: null,
                type: 'video',
                caption: { [LOCALE]: '' },
                video_url: videoUrl.trim(),
                token: null,
                previewUrl: null,
            },
        ]);

        setVideoUrl('');
    };

    const updateRow = (key: string, patch: Partial<ItemRow>) =>
        setData(
            'items',
            data.items.map((item) =>
                item.key === key ? { ...item, ...patch } : item,
            ),
        );

    const removeRow = (key: string) =>
        setData(
            'items',
            data.items.filter((item) => item.key !== key),
        );

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        put(syncGalleryItems(gallery.id).url, { preserveScroll: true });
    };

    const itemError = (index: number) =>
        errors[`items.${index}.token` as keyof typeof errors] ??
        errors[`items.${index}.video_url` as keyof typeof errors];

    return (
        <>
            <PageHeader
                title={`Isi Album: ${label}`}
                description="Unggah foto, tambahkan video, atur caption, lalu susun urutannya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Galeri', href: galleriesIndex() },
                    { title: label, href: galleryItemsEdit(gallery.id) },
                ]}
            />

            <form onSubmit={submit} className="space-y-5">
                <Panel
                    title="Tambah Foto"
                    description="Unggah beberapa foto sekaligus; semuanya masuk ke urutan terakhir."
                >
                    <MultiImageUploader
                        kind="gallery"
                        onChange={addImages}
                        hint="JPG, PNG, atau WEBP. Maksimal 20 berkas per unggahan."
                    />
                </Panel>

                <Panel
                    title="Tambah Video"
                    description="Tempel alamat video YouTube atau Vimeo. Video berkas besar sebaiknya tetap di penyedia luar agar halaman tetap ringan."
                >
                    <div className="flex flex-wrap items-end gap-2">
                        <div className="grid min-w-64 flex-1 gap-1.5">
                            <Label htmlFor="video_url">Alamat video</Label>
                            <Input
                                id="video_url"
                                dir="ltr"
                                value={videoUrl}
                                onChange={(event) =>
                                    setVideoUrl(event.target.value)
                                }
                                placeholder="https://www.youtube.com/watch?v=..."
                            />
                        </div>
                        <Button
                            type="button"
                            variant="create"
                            onClick={addVideo}
                            disabled={videoUrl.trim() === ''}
                        >
                            <Plus />
                            Tambah Video
                        </Button>
                    </div>
                </Panel>

                <Panel
                    title="Isi Album"
                    description="Geser untuk mengubah urutan tampil. Perubahan tersimpan saat tombol simpan ditekan."
                >
                    {data.items.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            Album masih kosong. Unggah foto atau tambahkan video
                            di atas.
                        </p>
                    ) : (
                        <SortableList
                            items={data.items}
                            itemId={(item) => item.key}
                            onReorder={(next) => setData('items', next)}
                        >
                            {(item, index) => (
                                <div className="flex flex-wrap items-center gap-3">
                                    {item.type === 'video' ? (
                                        <span
                                            aria-hidden
                                            className="flex size-12 shrink-0 items-center justify-center rounded-[var(--radius)] bg-muted text-muted-foreground"
                                        >
                                            <Film className="size-5" />
                                        </span>
                                    ) : item.previewUrl ? (
                                        <img
                                            src={item.previewUrl}
                                            alt=""
                                            className="size-12 shrink-0 rounded-[var(--radius)] border border-border object-cover"
                                        />
                                    ) : (
                                        <span
                                            aria-hidden
                                            className="flex size-12 shrink-0 items-center justify-center rounded-[var(--radius)] border border-dashed border-border text-[11px] text-muted-foreground"
                                        >
                                            Baru
                                        </span>
                                    )}

                                    <div className="grid min-w-56 flex-1 gap-1.5">
                                        <Input
                                            value={item.caption[LOCALE] ?? ''}
                                            onChange={(event) =>
                                                updateRow(item.key, {
                                                    caption: {
                                                        [LOCALE]:
                                                            event.target.value,
                                                    },
                                                })
                                            }
                                            placeholder="Caption singkat"
                                        />
                                        {item.type === 'video' && (
                                            <Input
                                                dir="ltr"
                                                value={item.video_url}
                                                onChange={(event) =>
                                                    updateRow(item.key, {
                                                        video_url:
                                                            event.target.value,
                                                    })
                                                }
                                                placeholder="https://…"
                                            />
                                        )}
                                        {itemError(index) && (
                                            <p className="text-xs text-destructive">
                                                {itemError(index)}
                                            </p>
                                        )}
                                    </div>

                                    <div className="flex items-center gap-2">
                                        <StatusBadge
                                            label={
                                                item.type === 'video'
                                                    ? 'Video'
                                                    : 'Gambar'
                                            }
                                            tone={
                                                item.type === 'video'
                                                    ? 'info'
                                                    : 'muted'
                                            }
                                        />
                                        <Button
                                            type="button"
                                            variant="delete"
                                            size="sm"
                                            onClick={() => removeRow(item.key)}
                                        >
                                            <Trash2 />
                                            Hapus
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </SortableList>
                    )}
                </Panel>

                <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                    <Button asChild variant="cancel" type="button">
                        <Link href={galleriesIndex()}>
                            <X />
                            Kembali
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        variant="save"
                        disabled={processing}
                        data-test="save-gallery-items-button"
                    >
                        {processing ? <Spinner /> : <Save />}
                        {processing ? 'Menyimpan...' : 'Simpan Isi Album'}
                    </Button>
                </div>
            </form>
        </>
    );
}
