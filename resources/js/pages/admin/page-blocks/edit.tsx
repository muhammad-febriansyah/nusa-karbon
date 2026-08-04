import { Link, useForm } from '@inertiajs/react';
import { Plus, Save, Trash2, X } from 'lucide-react';
import RichTextEditor from '@/components/form/rich-text-editor';
import PageHeader from '@/components/shared/page-header';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import { useMediaPicker } from '@/components/upload/media-picker';
import { dashboard } from '@/routes/admin';
import {
    edit as blockEdit,
    index as blocksIndex,
    update as updateBlock,
} from '@/routes/admin/page-blocks';
import type { PageBlock, Translated } from '@/types/models';

const LOCALE = 'id';

type Props = {
    block: PageBlock;
};

type PageBlockFormData = {
    title: Translated;
    content: Translated;
    extra: {
        items?: string[];
        value?: string;
        unit?: string;
    };
    image_path_token: string | null;
    remove_image_path: boolean;
    is_active: boolean;
};

export default function PageBlockEdit({ block }: Props) {
    const { pickImage, dialog: mediaPickerDialog } = useMediaPicker();
    const { data, setData, put, processing, errors } =
        useForm<PageBlockFormData>({
            title: { [LOCALE]: block.title?.[LOCALE] ?? '' },
            content: { [LOCALE]: block.content?.[LOCALE] ?? '' },
            extra: {
                items: block.extra?.items ?? [],
                value: block.extra?.value ?? '',
                unit: block.extra?.unit ?? '',
            },
            image_path_token: null,
            remove_image_path: false,
            is_active: block.is_active,
        });

    const label = block.title?.[LOCALE] ?? block.block_key;

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        put(updateBlock(block.id).url);
    };

    const titleError = errors[`title.${LOCALE}` as keyof typeof errors];
    const contentError = errors[`content.${LOCALE}` as keyof typeof errors];

    const items = data.extra.items ?? [];

    const setItems = (next: string[]) =>
        setData('extra', { ...data.extra, items: next });

    const currentImage =
        data.remove_image_path || data.image_path_token
            ? null
            : block.image_url;

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description={`Blok ${block.type_label} pada halaman ${block.page_key}. Kunci blok tidak dapat diubah.`}
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Blok Halaman', href: blocksIndex() },
                    { title: label, href: blockEdit(block.id) },
                ]}
            />

            <form onSubmit={submit} className="space-y-5">
                {mediaPickerDialog}

                <Panel
                    title="Isi Blok"
                    description="Judul dan isi yang tampil di halaman publik."
                >
                    <div className="grid gap-4">
                        <div className="grid gap-1.5">
                            <Label htmlFor="title">Judul</Label>
                            <Input
                                id="title"
                                value={data.title[LOCALE] ?? ''}
                                onChange={(event) =>
                                    setData('title', {
                                        [LOCALE]: event.target.value,
                                    })
                                }
                                aria-invalid={Boolean(titleError)}
                            />
                            {titleError && (
                                <p className="text-xs text-destructive">
                                    {titleError}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-1.5">
                            <Label htmlFor="content">Isi</Label>
                            {block.type === 'rich_text' ? (
                                <RichTextEditor
                                    value={data.content[LOCALE] ?? ''}
                                    onChange={(value) =>
                                        setData('content', { [LOCALE]: value })
                                    }
                                    onPickImage={pickImage}
                                />
                            ) : (
                                <Textarea
                                    id="content"
                                    rows={4}
                                    value={data.content[LOCALE] ?? ''}
                                    onChange={(event) =>
                                        setData('content', {
                                            [LOCALE]: event.target.value,
                                        })
                                    }
                                    aria-invalid={Boolean(contentError)}
                                />
                            )}
                            {contentError && (
                                <p className="text-xs text-destructive">
                                    {contentError}
                                </p>
                            )}
                        </div>
                    </div>
                </Panel>

                {block.type === 'list' && (
                    <Panel
                        title="Butir Daftar"
                        description="Setiap butir tampil sebagai satu baris berpoin."
                        action={
                            <Button
                                type="button"
                                variant="create"
                                size="sm"
                                onClick={() => setItems([...items, ''])}
                            >
                                <Plus />
                                Tambah Butir
                            </Button>
                        }
                    >
                        {items.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Belum ada butir. Tambahkan minimal satu agar
                                blok ini tidak kosong di halaman publik.
                            </p>
                        ) : (
                            <div className="grid gap-2">
                                {items.map((item, index) => (
                                    <div
                                        key={index}
                                        className="flex items-center gap-2"
                                    >
                                        <Input
                                            value={item}
                                            onChange={(event) =>
                                                setItems(
                                                    items.map((value, i) =>
                                                        i === index
                                                            ? event.target.value
                                                            : value,
                                                    ),
                                                )
                                            }
                                            placeholder={`Butir ${index + 1}`}
                                        />
                                        <Button
                                            type="button"
                                            variant="delete"
                                            size="sm"
                                            onClick={() =>
                                                setItems(
                                                    items.filter(
                                                        (_, i) => i !== index,
                                                    ),
                                                )
                                            }
                                        >
                                            <Trash2 />
                                            Hapus
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </Panel>
                )}

                {block.type === 'stat' && (
                    <Panel
                        title="Angka Sorotan"
                        description="Angka besar beserta satuannya, misalnya 20 FT atau 24 ton."
                    >
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="grid gap-1.5">
                                <Label htmlFor="extra_value">Nilai</Label>
                                <Input
                                    id="extra_value"
                                    dir="ltr"
                                    value={data.extra.value ?? ''}
                                    onChange={(event) =>
                                        setData('extra', {
                                            ...data.extra,
                                            value: event.target.value,
                                        })
                                    }
                                    placeholder="Contoh: 20"
                                />
                            </div>

                            <div className="grid gap-1.5">
                                <Label htmlFor="extra_unit">Satuan</Label>
                                <Input
                                    id="extra_unit"
                                    dir="ltr"
                                    value={data.extra.unit ?? ''}
                                    onChange={(event) =>
                                        setData('extra', {
                                            ...data.extra,
                                            unit: event.target.value,
                                        })
                                    }
                                    placeholder="Contoh: FT container"
                                />
                            </div>
                        </div>
                    </Panel>
                )}

                {block.type === 'text_image' && (
                    <Panel
                        title="Gambar"
                        description="Gambar pendamping teks pada blok ini."
                    >
                        <div className="grid gap-2">
                            {currentImage && (
                                <div className="flex items-center gap-3">
                                    <img
                                        src={currentImage}
                                        alt="Gambar blok saat ini"
                                        className="h-20 rounded-[var(--radius)] border border-border object-cover"
                                    />
                                    <Button
                                        type="button"
                                        variant="delete"
                                        size="sm"
                                        onClick={() =>
                                            setData('remove_image_path', true)
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
                                    setData('image_path_token', token)
                                }
                            />
                        </div>
                    </Panel>
                )}

                <Panel title="Status">
                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="is_active">Aktif</Label>
                            <p className="text-xs text-muted-foreground">
                                Blok nonaktif disembunyikan dari halaman publik
                                tanpa menghapus isinya.
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
                        <Link href={blocksIndex()}>
                            <X />
                            Batal
                        </Link>
                    </Button>
                    <Button
                        type="submit"
                        variant="save"
                        disabled={processing}
                        data-test="save-page-block-button"
                    >
                        {processing ? <Spinner /> : <Save />}
                        {processing ? 'Menyimpan...' : 'Simpan Perubahan'}
                    </Button>
                </div>
            </form>
        </>
    );
}
