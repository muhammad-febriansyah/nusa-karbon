import { Link, useForm } from '@inertiajs/react';
import { Save, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import DatePicker from '@/components/form/date-picker';
import RichTextEditor from '@/components/form/rich-text-editor';
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
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import { useMediaPicker } from '@/components/upload/media-picker';
import {
    index as postsIndex,
    store as storePost,
    update as updatePost,
} from '@/routes/admin/posts';
import type {
    Post,
    PostCategory,
    SelectOption,
    Translated,
} from '@/types/models';

const LOCALE = 'id';

const NO_CATEGORY = 'none';

const slugify = (value: string) =>
    value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

type Props = {
    post: Post | null;
    categories: PostCategory[];
    statuses: SelectOption[];
};

type PostFormData = {
    post_category_id: string;
    title: Translated;
    slug: Translated;
    excerpt: Translated;
    content: Translated;
    meta_title: Translated;
    meta_description: Translated;
    status: string;
    published_at: string | null;
    tags: string[];
    cover_token: string | null;
    remove_cover: boolean;
};

export default function PostForm({ post, categories, statuses }: Props) {
    const isEdit = post !== null;
    const [tagInput, setTagInput] = useState('');
    const { pickImage, dialog: mediaPickerDialog } = useMediaPicker();
    const {
        data,
        setData,
        transform,
        post: submitPost,
        put,
        processing,
        errors,
    } = useForm<PostFormData>({
        post_category_id:
            post?.post_category_id === null ||
            post?.post_category_id === undefined
                ? NO_CATEGORY
                : String(post.post_category_id),
        title: { [LOCALE]: post?.title?.[LOCALE] ?? '' },
        slug: { [LOCALE]: post?.slug?.[LOCALE] ?? '' },
        excerpt: { [LOCALE]: post?.excerpt?.[LOCALE] ?? '' },
        content: { [LOCALE]: post?.content?.[LOCALE] ?? '' },
        meta_title: { [LOCALE]: post?.meta_title?.[LOCALE] ?? '' },
        meta_description: {
            [LOCALE]: post?.meta_description?.[LOCALE] ?? '',
        },
        status: post?.status ?? 'draft',
        published_at: post?.published_at?.slice(0, 10) ?? null,
        tags: post?.tags ?? [],
        cover_token: null,
        remove_cover: false,
    });

    // Select shadcn tidak menerima nilai kosong, jadi "Tanpa kategori" dikirim
    // ke server sebagai null.
    transform((payload) => ({
        ...payload,
        post_category_id:
            payload.post_category_id === NO_CATEGORY
                ? null
                : Number(payload.post_category_id),
    }));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updatePost(post.id).url);

            return;
        }

        submitPost(storePost().url);
    };

    const errorFor = (key: string) => errors[key as keyof typeof errors];

    const currentCover =
        data.remove_cover || data.cover_token
            ? null
            : (post?.cover_url ?? null);

    const addTag = () => {
        const value = tagInput.trim();

        if (value === '' || data.tags.includes(value)) {
            setTagInput('');

            return;
        }

        setData('tags', [...data.tags, value]);
        setTagInput('');
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            {mediaPickerDialog}

            <Panel
                title="Judul & Ringkasan"
                description="Judul yang dibaca pengunjung dan slug yang dipakai di alamat halaman."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="title">
                            Judul artikel
                            <span aria-hidden className="text-destructive">
                                *
                            </span>
                        </Label>
                        <Input
                            id="title"
                            value={data.title[LOCALE] ?? ''}
                            onChange={(event) => {
                                const value = event.target.value;

                                setData((current) => ({
                                    ...current,
                                    title: { [LOCALE]: value },
                                    slug:
                                        isEdit ||
                                        current.slug[LOCALE] !==
                                            slugify(current.title[LOCALE] ?? '')
                                            ? current.slug
                                            : { [LOCALE]: slugify(value) },
                                }));
                            }}
                            placeholder="Contoh: Memahami Incoterms untuk Briket"
                            aria-invalid={Boolean(errorFor(`title.${LOCALE}`))}
                        />
                        {errorFor(`title.${LOCALE}`) && (
                            <p className="text-xs text-destructive">
                                {errorFor(`title.${LOCALE}`)}
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
                            aria-invalid={Boolean(errorFor(`slug.${LOCALE}`))}
                        />
                        {errorFor(`slug.${LOCALE}`) && (
                            <p className="text-xs text-destructive">
                                {errorFor(`slug.${LOCALE}`)}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5 sm:col-span-2">
                        <Label htmlFor="excerpt">Ringkasan</Label>
                        <Textarea
                            id="excerpt"
                            rows={2}
                            value={data.excerpt[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('excerpt', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Satu sampai dua kalimat untuk kartu artikel dan hasil pencarian."
                        />
                    </div>
                </div>
            </Panel>

            <Panel
                title="Isi Artikel"
                description="HTML dibersihkan di server, jadi tempelan dari Word tidak membawa gaya asing."
            >
                <RichTextEditor
                    value={data.content[LOCALE] ?? ''}
                    onChange={(value) =>
                        setData('content', { [LOCALE]: value })
                    }
                    onPickImage={pickImage}
                    placeholder="Tulis artikel di sini…"
                />
                {errorFor(`content.${LOCALE}`) && (
                    <p className="mt-2 text-xs text-destructive">
                        {errorFor(`content.${LOCALE}`)}
                    </p>
                )}
            </Panel>

            <Panel
                title="Klasifikasi"
                description="Kategori dan tag membantu pembaca menemukan artikel terkait."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="post_category_id">Kategori</Label>
                        <Select
                            value={data.post_category_id}
                            onValueChange={(value) =>
                                setData('post_category_id', value)
                            }
                        >
                            <SelectTrigger id="post_category_id">
                                <SelectValue placeholder="Pilih kategori" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={NO_CATEGORY}>
                                    Tanpa kategori
                                </SelectItem>
                                {categories.map((category) => (
                                    <SelectItem
                                        key={category.id}
                                        value={String(category.id)}
                                    >
                                        {category.name?.[LOCALE] ?? '—'}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="tags">Tag</Label>
                        <div className="flex gap-2">
                            <Input
                                id="tags"
                                value={tagInput}
                                onChange={(event) =>
                                    setTagInput(event.target.value)
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter') {
                                        event.preventDefault();
                                        addTag();
                                    }
                                }}
                                placeholder="Ketik lalu tekan Enter"
                            />
                            <Button
                                type="button"
                                variant="create"
                                onClick={addTag}
                                disabled={tagInput.trim() === ''}
                            >
                                Tambah
                            </Button>
                        </div>
                        {data.tags.length > 0 && (
                            <div className="flex flex-wrap gap-1.5">
                                {data.tags.map((tag) => (
                                    <button
                                        key={tag}
                                        type="button"
                                        onClick={() =>
                                            setData(
                                                'tags',
                                                data.tags.filter(
                                                    (item) => item !== tag,
                                                ),
                                            )
                                        }
                                        className="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-[11px] text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        {tag}
                                        <X aria-hidden className="size-3" />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Penerbitan"
                description="Artikel terjadwal terbit otomatis saat waktunya tiba."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="status">Status</Label>
                        <Select
                            value={data.status}
                            onValueChange={(value) => setData('status', value)}
                        >
                            <SelectTrigger id="status">
                                <SelectValue placeholder="Pilih status" />
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
                        {errors.status && (
                            <p className="text-xs text-destructive">
                                {errors.status}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="published_at">Tanggal tayang</Label>
                        <DatePicker
                            id="published_at"
                            value={data.published_at}
                            onChange={(value) => setData('published_at', value)}
                            placeholder="Kosongkan untuk tayang sekarang"
                            aria-invalid={Boolean(errors.published_at)}
                        />
                        {errors.published_at && (
                            <p className="text-xs text-destructive">
                                {errors.published_at}
                            </p>
                        )}
                    </div>
                </div>
            </Panel>

            <Panel
                title="Sampul & SEO"
                description="Gambar sampul dan teks yang dipakai mesin pencari."
            >
                <div className="grid gap-4">
                    <div className="grid gap-2">
                        <Label>Sampul artikel</Label>
                        {currentCover && (
                            <div className="flex items-center gap-3">
                                <img
                                    src={currentCover}
                                    alt="Sampul saat ini"
                                    className="h-20 rounded-[var(--radius)] border border-border object-cover"
                                />
                                <Button
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    onClick={() =>
                                        setData('remove_cover', true)
                                    }
                                >
                                    <Trash2 />
                                    Hapus Sampul
                                </Button>
                            </div>
                        )}
                        <ImageUploader
                            kind="product_cover"
                            onChange={(token) => setData('cover_token', token)}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-1.5">
                            <Label htmlFor="meta_title">Meta title</Label>
                            <Input
                                id="meta_title"
                                value={data.meta_title[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('meta_title', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Kosongkan untuk memakai judul artikel"
                            />
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="meta_description">
                                Meta description
                            </Label>
                            <Textarea
                                id="meta_description"
                                rows={2}
                                value={data.meta_description[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('meta_description', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                placeholder="Maksimal 160 karakter agar tidak terpotong di hasil pencarian."
                            />
                        </div>
                    </div>
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={postsIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-post-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan Artikel'}
                </Button>
            </div>
        </form>
    );
}
