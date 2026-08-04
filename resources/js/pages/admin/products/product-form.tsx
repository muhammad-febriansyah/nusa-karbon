import { Link, useForm } from '@inertiajs/react';
import { Save, TriangleAlert, X } from 'lucide-react';
import ComboboxSelect from '@/components/form/combobox-select';
import InputDecimal from '@/components/form/input-decimal';
import RichTextEditor from '@/components/form/rich-text-editor';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import MultiImageUploader from '@/components/upload/multi-image-uploader';
import {
    store as storeProduct,
    update as updateProduct,
    index as productsIndex,
} from '@/routes/admin/products';
import type {
    DocumentRow,
    Product,
    SelectOption,
    SpecificationRow,
    SpecificationTemplateRow,
    Translated,
} from '@/types/models';
import DocumentRepeater from './document-repeater';
import SpecificationRepeater from './specification-repeater';

const LOCALE = 'id';

type Props = {
    product: Product | null;
    categories: SelectOption[];
    specificationTemplate: SpecificationTemplateRow[];
    documentTypes: SelectOption[];
    packagingOptions: SelectOption[];
};

type ProductForm = {
    product_category_id: string | null;
    code: string;
    name: Translated;
    slug: Translated;
    short_description: Translated;
    description: Translated;
    meta_title: Translated;
    meta_description: Translated;
    shape: string;
    size_mm: string;
    master_carton_weight_kg: number | null;
    master_carton_tare_kg: number | null;
    master_carton_cbm: number | null;
    master_carton_length_cm: number | null;
    master_carton_width_cm: number | null;
    master_carton_height_cm: number | null;
    inner_box_weight_kg: number | null;
    inner_boxes_per_carton: number | null;
    moq_kg: number | null;
    is_active: boolean;
    is_featured: boolean;
    cover_tokens: string[];
    gallery_tokens: string[];
    removed_media_ids: number[];
    specifications: SpecificationRow[];
    documents: DocumentRow[];
    packaging_option_ids: string[];
};

const toForm = (product: Product | null): ProductForm => ({
    product_category_id: product?.product_category_id
        ? String(product.product_category_id)
        : null,
    code: product?.code ?? '',
    name: { [LOCALE]: product?.name?.[LOCALE] ?? '' },
    slug: { [LOCALE]: product?.slug?.[LOCALE] ?? '' },
    short_description: { [LOCALE]: product?.short_description?.[LOCALE] ?? '' },
    description: { [LOCALE]: product?.description?.[LOCALE] ?? '' },
    meta_title: { [LOCALE]: product?.meta_title?.[LOCALE] ?? '' },
    meta_description: { [LOCALE]: product?.meta_description?.[LOCALE] ?? '' },
    shape: product?.shape ?? '',
    size_mm: product?.size_mm ?? '',
    master_carton_weight_kg: product?.master_carton_weight_kg ?? null,
    master_carton_tare_kg: product?.master_carton_tare_kg ?? null,
    master_carton_cbm: product?.master_carton_cbm ?? null,
    master_carton_length_cm: product?.master_carton_length_cm ?? null,
    master_carton_width_cm: product?.master_carton_width_cm ?? null,
    master_carton_height_cm: product?.master_carton_height_cm ?? null,
    inner_box_weight_kg: product?.inner_box_weight_kg ?? null,
    inner_boxes_per_carton: product?.inner_boxes_per_carton ?? null,
    moq_kg: product?.moq_kg ?? null,
    is_active: product?.is_active ?? true,
    is_featured: product?.is_featured ?? false,
    cover_tokens: [],
    gallery_tokens: [],
    removed_media_ids: [],
    specifications: (product?.specifications ?? []).map((spec) => ({
        key: `spec-${spec.id}`,
        id: spec.id,
        parameter: spec.parameter,
        value: spec.value,
        unit: spec.unit ?? '',
        is_highlighted: spec.is_highlighted,
    })),
    documents: (product?.documents ?? []).map((document) => ({
        key: `doc-${document.id}`,
        id: document.id,
        type: document.type,
        title: document.title,
        batch_reference: document.batch_reference ?? '',
        issued_at: document.issued_at,
        is_active: document.is_active,
        token: null,
        file: {
            name: document.file_name,
            size: document.file_size,
            url: document.url,
        },
    })),
    packaging_option_ids: (product?.packaging_option_ids ?? []).map(String),
});

export default function ProductForm({
    product,
    categories,
    specificationTemplate,
    documentTypes,
    packagingOptions,
}: Props) {
    const isEdit = product !== null;
    const { data, setData, post, put, processing, errors } =
        useForm<ProductForm>(toForm(product));

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(updateProduct(product.id).url);

            return;
        }

        post(storeProduct().url);
    };

    const nameError = errors[`name.${LOCALE}` as keyof typeof errors];
    const slugError = errors[`slug.${LOCALE}` as keyof typeof errors];

    // Produk baru muncul di Container Calculator hanya bila keduanya terisi.
    const cartonIncomplete =
        !data.master_carton_weight_kg || !data.master_carton_cbm;

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel
                title="Informasi Dasar"
                description="Identitas produk dan pengelompokannya."
            >
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-1.5">
                        <Label htmlFor="code">
                            Kode produk
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
                            placeholder="Contoh: SHISHA-PREM-26"
                            aria-invalid={Boolean(errors.code)}
                        />
                        {errors.code && (
                            <p className="text-xs text-destructive">
                                {errors.code}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="product_category_id">Kategori</Label>
                        <ComboboxSelect
                            id="product_category_id"
                            options={categories}
                            value={data.product_category_id}
                            onChange={(value) =>
                                setData('product_category_id', value)
                            }
                            placeholder="Pilih kategori — boleh dikosongkan"
                            searchPlaceholder="Ketik nama kategori…"
                            aria-invalid={Boolean(errors.product_category_id)}
                        />
                        {errors.product_category_id && (
                            <p className="text-xs text-destructive">
                                {errors.product_category_id}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="name">
                            Nama produk
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
                            placeholder="Contoh: Shisha Premium 26mm"
                            aria-invalid={Boolean(nameError)}
                        />
                        {nameError && (
                            <p className="text-xs text-destructive">
                                {nameError}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="slug">Slug URL</Label>
                        <Input
                            id="slug"
                            dir="ltr"
                            value={data.slug[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('slug', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Contoh: shisha-premium-26mm — kosongkan untuk dibuat otomatis"
                            aria-invalid={Boolean(slugError)}
                        />
                        {slugError && (
                            <p className="text-xs text-destructive">
                                {slugError}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="shape">Bentuk</Label>
                        <Input
                            id="shape"
                            value={data.shape}
                            onChange={(event) =>
                                setData('shape', event.target.value)
                            }
                            placeholder="Contoh: Cube, Hexagonal, atau Finger"
                            aria-invalid={Boolean(errors.shape)}
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="size_mm">Ukuran</Label>
                        <Input
                            id="size_mm"
                            dir="ltr"
                            value={data.size_mm}
                            onChange={(event) =>
                                setData('size_mm', event.target.value)
                            }
                            placeholder="Contoh: 26 x 26 x 26"
                            aria-invalid={Boolean(errors.size_mm)}
                        />
                    </div>
                </div>

                <div className="mt-4 grid gap-4">
                    <div className="grid gap-1.5">
                        <Label htmlFor="short_description">
                            Deskripsi singkat
                        </Label>
                        <Textarea
                            id="short_description"
                            rows={2}
                            value={data.short_description[LOCALE] ?? ''}
                            onChange={(event) =>
                                setData('short_description', {
                                    [LOCALE]: event.target.value,
                                })
                            }
                            placeholder="Contoh: Briket shisha premium, abu rendah, bakar hingga 2 jam."
                        />
                        <p className="text-xs text-muted-foreground">
                            Tampil di kartu produk pada daftar website publik.
                        </p>
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="description">Deskripsi lengkap</Label>
                        <RichTextEditor
                            id="description"
                            value={data.description[LOCALE] ?? ''}
                            onChange={(html) =>
                                setData('description', { [LOCALE]: html })
                            }
                            placeholder="Jelaskan keunggulan produk, bahan baku, dan penggunaannya."
                        />
                    </div>
                </div>
            </Panel>

            <Panel
                title="Data Pengiriman"
                description="Dipakai Container Calculator untuk menghitung muatan per kontainer."
            >
                {cartonIncomplete && (
                    <div className="mb-4 flex items-start gap-2.5 rounded-[var(--radius)] border border-s-[3px] border-warning/25 border-s-warning bg-warning/10 px-3.5 py-2.5">
                        <TriangleAlert
                            aria-hidden
                            className="mt-0.5 size-4 shrink-0 text-warning"
                        />
                        <p className="text-[13px] text-warning">
                            Berat dan volume master carton belum lengkap. Produk
                            ini tidak akan muncul di Container Calculator sampai
                            keduanya diisi.
                        </p>
                    </div>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_weight_kg">
                            Berat isi per master carton
                        </Label>
                        <InputDecimal
                            id="master_carton_weight_kg"
                            unit="kg"
                            precision={3}
                            value={data.master_carton_weight_kg}
                            onChange={(value) =>
                                setData('master_carton_weight_kg', value)
                            }
                            placeholder="Contoh: 20"
                            aria-invalid={Boolean(
                                errors.master_carton_weight_kg,
                            )}
                        />
                        {errors.master_carton_weight_kg && (
                            <p className="text-xs text-destructive">
                                {errors.master_carton_weight_kg}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_cbm">
                            Volume per master carton
                        </Label>
                        <InputDecimal
                            id="master_carton_cbm"
                            unit="CBM"
                            precision={4}
                            value={data.master_carton_cbm}
                            onChange={(value) =>
                                setData('master_carton_cbm', value)
                            }
                            placeholder="Contoh: 0,043"
                            aria-invalid={Boolean(errors.master_carton_cbm)}
                        />
                        {errors.master_carton_cbm && (
                            <p className="text-xs text-destructive">
                                {errors.master_carton_cbm}
                            </p>
                        )}
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_tare_kg">
                            Berat karton kosong
                        </Label>
                        <InputDecimal
                            id="master_carton_tare_kg"
                            unit="kg"
                            precision={3}
                            value={data.master_carton_tare_kg}
                            onChange={(value) =>
                                setData('master_carton_tare_kg', value)
                            }
                            placeholder="Contoh: 1,2"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_length_cm">Panjang</Label>
                        <InputDecimal
                            id="master_carton_length_cm"
                            unit="cm"
                            precision={2}
                            value={data.master_carton_length_cm}
                            onChange={(value) =>
                                setData('master_carton_length_cm', value)
                            }
                            placeholder="Contoh: 40"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_width_cm">Lebar</Label>
                        <InputDecimal
                            id="master_carton_width_cm"
                            unit="cm"
                            precision={2}
                            value={data.master_carton_width_cm}
                            onChange={(value) =>
                                setData('master_carton_width_cm', value)
                            }
                            placeholder="Contoh: 30"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="master_carton_height_cm">Tinggi</Label>
                        <InputDecimal
                            id="master_carton_height_cm"
                            unit="cm"
                            precision={2}
                            value={data.master_carton_height_cm}
                            onChange={(value) =>
                                setData('master_carton_height_cm', value)
                            }
                            placeholder="Contoh: 35"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="inner_box_weight_kg">
                            Berat inner box
                        </Label>
                        <InputDecimal
                            id="inner_box_weight_kg"
                            unit="kg"
                            precision={3}
                            value={data.inner_box_weight_kg}
                            onChange={(value) =>
                                setData('inner_box_weight_kg', value)
                            }
                            placeholder="Contoh: 1"
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="inner_boxes_per_carton">
                            Inner box per carton
                        </Label>
                        <Input
                            id="inner_boxes_per_carton"
                            dir="ltr"
                            inputMode="numeric"
                            className="text-end tabular-nums"
                            value={data.inner_boxes_per_carton ?? ''}
                            onChange={(event) =>
                                setData(
                                    'inner_boxes_per_carton',
                                    event.target.value === ''
                                        ? null
                                        : Number(
                                              event.target.value.replace(
                                                  /\D/g,
                                                  '',
                                              ),
                                          ),
                                )
                            }
                            placeholder="Contoh: 20"
                            aria-invalid={Boolean(
                                errors.inner_boxes_per_carton,
                            )}
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="moq_kg">MOQ</Label>
                        <InputDecimal
                            id="moq_kg"
                            unit="kg"
                            precision={2}
                            value={data.moq_kg}
                            onChange={(value) => setData('moq_kg', value)}
                            placeholder="Contoh: 1000"
                        />
                    </div>
                </div>
            </Panel>

            <Panel
                title="Spesifikasi Teknis"
                description="Parameter yang paling sering ditanyakan buyer. Urutannya menentukan tampilan di halaman produk."
            >
                <SpecificationRepeater
                    rows={data.specifications}
                    onChange={(rows) => setData('specifications', rows)}
                    template={specificationTemplate}
                    errors={errors as Record<string, string>}
                />
            </Panel>

            <Panel
                title="Opsi Kemasan"
                description="Pilihan kemasan yang tersedia untuk produk ini; tampil di halaman produk dan form RFQ."
            >
                <div className="grid gap-1.5">
                    <Label htmlFor="packaging_option_ids">
                        Kemasan tersedia
                    </Label>
                    <ComboboxSelect
                        id="packaging_option_ids"
                        multiple
                        options={packagingOptions}
                        value={data.packaging_option_ids}
                        onChange={(value) =>
                            setData('packaging_option_ids', value)
                        }
                        placeholder="Pilih satu atau beberapa kemasan — boleh dikosongkan"
                        searchPlaceholder="Ketik nama kemasan…"
                    />
                    <p className="text-xs text-muted-foreground">
                        Hanya kemasan berstatus aktif yang muncul di daftar ini.
                    </p>
                </div>
            </Panel>

            <Panel
                title="Dokumen"
                description="COA, MSDS, SHT, dan Packing List yang bisa diunduh buyer dari halaman produk."
            >
                <DocumentRepeater
                    rows={data.documents}
                    onChange={(rows) => setData('documents', rows)}
                    types={documentTypes}
                    errors={errors as Record<string, string>}
                />
            </Panel>

            <Panel
                title="Media"
                description="Gambar sampul tampil di kartu produk, galeri tampil di halaman detail."
            >
                <div className="grid gap-5">
                    <div className="grid gap-1.5">
                        <Label>Gambar sampul</Label>
                        <ImageUploader
                            kind="product_cover"
                            currentUrl={product?.cover_url}
                            onChange={(token) =>
                                setData('cover_tokens', token ? [token] : [])
                            }
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label>Galeri</Label>
                        {product && product.gallery.length > 0 && (
                            <div className="mb-2 flex flex-wrap gap-2">
                                {product.gallery
                                    .filter(
                                        (item) =>
                                            !data.removed_media_ids.includes(
                                                item.id,
                                            ),
                                    )
                                    .map((item) => (
                                        <span
                                            key={item.id}
                                            className="relative inline-flex"
                                        >
                                            <img
                                                src={item.url}
                                                alt={item.name}
                                                className="size-20 rounded-[var(--radius)] border border-border object-cover"
                                            />
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    setData(
                                                        'removed_media_ids',
                                                        [
                                                            ...data.removed_media_ids,
                                                            item.id,
                                                        ],
                                                    )
                                                }
                                                className="absolute -end-1.5 -top-1.5 rounded-full bg-destructive p-1 text-destructive-foreground"
                                            >
                                                <X className="size-3" />
                                                <span className="sr-only">
                                                    Hapus {item.name}
                                                </span>
                                            </button>
                                        </span>
                                    ))}
                            </div>
                        )}
                        <MultiImageUploader
                            kind="gallery"
                            onChange={(tokens) =>
                                setData('gallery_tokens', tokens)
                            }
                        />
                    </div>
                </div>
            </Panel>

            <Panel
                title="SEO"
                description="Judul dan deskripsi yang muncul di hasil pencarian Google."
            >
                <div className="grid gap-4">
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
                            placeholder="Contoh: Shisha Premium 26mm — Briket Arang Batok Kelapa"
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
                            placeholder="Contoh: Briket shisha premium dari batok kelapa Indonesia. Abu rendah, tanpa aroma, siap ekspor."
                        />
                    </div>
                </div>
            </Panel>

            <Panel title="Status">
                <div className="grid gap-3">
                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="is_active">Aktif</Label>
                            <p className="text-xs text-muted-foreground">
                                Produk nonaktif tidak tampil di website publik.
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

                    <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                        <div className="space-y-0.5">
                            <Label htmlFor="is_featured">Produk unggulan</Label>
                            <p className="text-xs text-muted-foreground">
                                Produk unggulan tampil di beranda.
                            </p>
                        </div>
                        <Switch
                            id="is_featured"
                            checked={data.is_featured}
                            onCheckedChange={(checked) =>
                                setData('is_featured', checked)
                            }
                        />
                    </div>
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={productsIndex()}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-product-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : 'Simpan Produk'}
                </Button>
            </div>
        </form>
    );
}
