import { Link, useForm } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import { Save, Trash2, X } from 'lucide-react';
import Panel from '@/components/shared/panel';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import ImageUploader from '@/components/upload/image-uploader';
import type { UploadKind } from '@/lib/upload-rules';
import { toUrl } from '@/lib/utils';
import type { Translated } from '@/types/models';

const LOCALE = 'id';

type BaseField = {
    name: string;
    label: string;
    placeholder?: string;
    hint?: string;
    required?: boolean;
    /** Isi satu kolom penuh pada grid dua kolom. */
    wide?: boolean;
};

export type ContentField =
    | (BaseField & { type: 'text'; dir?: 'ltr' })
    | (BaseField & { type: 'translated' })
    | (BaseField & { type: 'translated-textarea'; rows?: number })
    | (BaseField & {
          type: 'image';
          kind: UploadKind;
          currentUrl: string | null;
          /** Foto orang ditampilkan bulat, logo dan gambar konten persegi. */
          rounded?: boolean;
      });

export type ContentFormValue = string | boolean | null | Translated;

type Props = {
    fields: ContentField[];
    /** Nilai awal tiap field; kunci sama dengan `name`. */
    initialValues: Record<string, ContentFormValue>;
    isActive: boolean;
    isEdit: boolean;
    submitHref: NonNullable<InertiaLinkProps['href']>;
    cancelHref: NonNullable<InertiaLinkProps['href']>;
    saveLabel: string;
    /** Kalimat penjelas di panel status. */
    activeDescription: string;
    panelTitle: string;
    panelDescription?: string;
};

/**
 * Form bersama untuk konten perusahaan berurutan. Field-nya dideskripsikan
 * per modul, sehingga keenam modul memakai tata letak, urutan tombol, dan
 * penanganan unggahan yang sama persis.
 *
 * @see docs/07-admin-ui-standards.md bagian 2
 * @see docs/09-tasks.md bagian 3.3
 */
export default function ContentForm({
    fields,
    initialValues,
    isActive,
    isEdit,
    submitHref,
    cancelHref,
    saveLabel,
    activeDescription,
    panelTitle,
    panelDescription,
}: Props) {
    const { data, setData, post, put, processing, errors } = useForm<
        Record<string, ContentFormValue>
    >({
        ...initialValues,
        is_active: isActive,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (isEdit) {
            put(toUrl(submitHref));

            return;
        }

        post(toUrl(submitHref));
    };

    const errorFor = (name: string): string | undefined =>
        errors[name as keyof typeof errors];

    const translatedValue = (name: string): string => {
        const value = data[name];

        if (value === null || typeof value !== 'object') {
            return '';
        }

        return value[LOCALE] ?? '';
    };

    const stringValue = (name: string): string => {
        const value = data[name];

        return typeof value === 'string' ? value : '';
    };

    return (
        <form onSubmit={submit} className="space-y-5">
            <Panel title={panelTitle} description={panelDescription}>
                <div className="grid gap-4 sm:grid-cols-2">
                    {fields.map((field) => {
                        const error =
                            field.type === 'translated' ||
                            field.type === 'translated-textarea'
                                ? (errorFor(`${field.name}.${LOCALE}`) ??
                                  errorFor(field.name))
                                : field.type === 'image'
                                  ? errorFor(`${field.name}_token`)
                                  : errorFor(field.name);

                        return (
                            <div
                                key={field.name}
                                className={
                                    field.wide || field.type !== 'text'
                                        ? 'grid gap-1.5 sm:col-span-2'
                                        : 'grid gap-1.5'
                                }
                            >
                                <Label htmlFor={field.name}>
                                    {field.label}
                                    {field.required && (
                                        <span
                                            aria-hidden
                                            className="text-destructive"
                                        >
                                            *
                                        </span>
                                    )}
                                </Label>

                                {field.type === 'text' && (
                                    <Input
                                        id={field.name}
                                        dir={field.dir}
                                        value={stringValue(field.name)}
                                        onChange={(event) =>
                                            setData(
                                                field.name,
                                                event.target.value,
                                            )
                                        }
                                        placeholder={field.placeholder}
                                        aria-invalid={Boolean(error)}
                                    />
                                )}

                                {field.type === 'translated' && (
                                    <Input
                                        id={field.name}
                                        value={translatedValue(field.name)}
                                        onChange={(event) =>
                                            setData(field.name, {
                                                [LOCALE]: event.target.value,
                                            })
                                        }
                                        placeholder={field.placeholder}
                                        aria-invalid={Boolean(error)}
                                    />
                                )}

                                {field.type === 'translated-textarea' && (
                                    <Textarea
                                        id={field.name}
                                        rows={field.rows ?? 3}
                                        value={translatedValue(field.name)}
                                        onChange={(event) =>
                                            setData(field.name, {
                                                [LOCALE]: event.target.value,
                                            })
                                        }
                                        placeholder={field.placeholder}
                                        aria-invalid={Boolean(error)}
                                    />
                                )}

                                {field.type === 'image' && (
                                    <ImageField
                                        field={field}
                                        removed={
                                            data[`remove_${field.name}`] ===
                                            true
                                        }
                                        replaced={
                                            typeof data[
                                                `${field.name}_token`
                                            ] === 'string'
                                        }
                                        onRemove={() =>
                                            setData(
                                                `remove_${field.name}`,
                                                true,
                                            )
                                        }
                                        onUpload={(token) =>
                                            setData(
                                                `${field.name}_token`,
                                                token,
                                            )
                                        }
                                    />
                                )}

                                {field.hint && !error && (
                                    <p className="text-xs text-muted-foreground">
                                        {field.hint}
                                    </p>
                                )}

                                {error && (
                                    <p className="text-xs text-destructive">
                                        {error}
                                    </p>
                                )}
                            </div>
                        );
                    })}
                </div>
            </Panel>

            <Panel title="Status">
                <div className="flex items-center justify-between rounded-[var(--radius)] border border-border p-3">
                    <div className="space-y-0.5">
                        <Label htmlFor="is_active">Aktif</Label>
                        <p className="text-xs text-muted-foreground">
                            {activeDescription}
                        </p>
                    </div>
                    <Switch
                        id="is_active"
                        checked={data.is_active === true}
                        onCheckedChange={(checked) =>
                            setData('is_active', checked)
                        }
                    />
                </div>
            </Panel>

            <div className="sticky bottom-0 -mx-4 flex justify-end gap-2 border-t border-border bg-card/95 px-4 py-3.5 backdrop-blur md:-mx-6 md:px-6">
                <Button asChild variant="cancel" type="button">
                    <Link href={cancelHref}>
                        <X />
                        Batal
                    </Link>
                </Button>
                <Button
                    type="submit"
                    variant="save"
                    disabled={processing}
                    data-test="save-content-button"
                >
                    {processing ? <Spinner /> : <Save />}
                    {processing
                        ? 'Menyimpan...'
                        : isEdit
                          ? 'Simpan Perubahan'
                          : saveLabel}
                </Button>
            </div>
        </form>
    );
}

function ImageField({
    field,
    removed,
    replaced,
    onRemove,
    onUpload,
}: {
    field: Extract<ContentField, { type: 'image' }>;
    removed: boolean;
    replaced: boolean;
    onRemove: () => void;
    onUpload: (token: string | null) => void;
}) {
    const current = removed || replaced ? null : field.currentUrl;

    return (
        <div className="grid gap-2">
            {current && (
                <div className="flex items-center gap-3">
                    <img
                        src={current}
                        alt="Berkas saat ini"
                        className={
                            field.rounded
                                ? 'size-16 rounded-full border border-border object-cover'
                                : 'size-16 rounded-[var(--radius)] border border-border object-contain p-1.5'
                        }
                    />
                    <Button
                        type="button"
                        variant="delete"
                        size="sm"
                        onClick={onRemove}
                    >
                        <Trash2 />
                        Hapus Berkas
                    </Button>
                </div>
            )}

            <ImageUploader
                kind={field.kind}
                onChange={onUpload}
                hint={field.hint}
            />
        </div>
    );
}
