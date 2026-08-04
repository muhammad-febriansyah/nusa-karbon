import {
    Download,
    FileSpreadsheet,
    FileText,
    Plus,
    Trash2,
    Upload,
} from 'lucide-react';
import { useState } from 'react';
import ComboboxSelect from '@/components/form/combobox-select';
import DatePicker from '@/components/form/date-picker';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import DocumentUploader from '@/components/upload/document-uploader';
import { formatBytes } from '@/lib/upload-rules';
import type { DocumentRow, SelectOption } from '@/types/models';

const LOCALE = 'id';

type Props = {
    rows: DocumentRow[];
    onChange: (rows: DocumentRow[]) => void;
    types: SelectOption[];
    /** Error validasi per baris, kunci mengikuti indeks larik. */
    errors?: Record<string, string>;
};

const localKey = () => `doc-${Math.random().toString(36).slice(2, 10)}`;

export const emptyDocument = (type: string): DocumentRow => ({
    key: localKey(),
    id: null,
    type,
    title: { [LOCALE]: '' },
    batch_reference: '',
    issued_at: null,
    is_active: true,
    token: null,
    file: null,
});

const isSpreadsheet = (name: string) => name.toLowerCase().endsWith('.xlsx');

export default function DocumentRepeater({
    rows,
    onChange,
    types,
    errors = {},
}: Props) {
    // Baris lama menampilkan berkasnya saja; uploader baru muncul setelah admin
    // memilih "Ganti Berkas", supaya tidak sengaja tertimpa.
    const [replacing, setReplacing] = useState<string[]>([]);

    const patch = (index: number, changes: Partial<DocumentRow>) =>
        onChange(
            rows.map((row, position) =>
                position === index ? { ...row, ...changes } : row,
            ),
        );

    const remove = (index: number) =>
        onChange(rows.filter((_, position) => position !== index));

    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    variant="create"
                    size="sm"
                    onClick={() =>
                        onChange([
                            ...rows,
                            emptyDocument(types[0]?.value ?? 'other'),
                        ])
                    }
                >
                    <Plus />
                    Tambah Dokumen
                </Button>

                <span className="text-xs text-muted-foreground">
                    Format PDF atau XLSX, maksimal 10 MB per berkas.
                </span>
            </div>

            {rows.length === 0 ? (
                <p className="rounded-[var(--radius)] border border-dashed border-input px-4 py-6 text-center text-sm text-muted-foreground">
                    Belum ada dokumen. Tambahkan COA, MSDS, SHT, atau Packing
                    List agar buyer bisa mengunduhnya dari halaman produk.
                </p>
            ) : (
                rows.map((row, index) => {
                    const showUploader =
                        row.file === null || replacing.includes(row.key);
                    const Icon =
                        row.file && isSpreadsheet(row.file.name)
                            ? FileSpreadsheet
                            : FileText;
                    const titleError =
                        errors[`documents.${index}.title.${LOCALE}`];

                    return (
                        <div
                            key={row.key}
                            className="grid gap-4 rounded-[var(--radius)] border border-border p-4"
                        >
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-1.5">
                                    <Label htmlFor={`doc-type-${row.key}`}>
                                        Tipe dokumen
                                    </Label>
                                    <ComboboxSelect
                                        id={`doc-type-${row.key}`}
                                        options={types}
                                        value={row.type}
                                        onChange={(value) =>
                                            patch(index, {
                                                type: value ?? 'other',
                                            })
                                        }
                                        clearable={false}
                                        placeholder="Pilih tipe dokumen"
                                        searchPlaceholder="Ketik tipe dokumen…"
                                        aria-invalid={Boolean(
                                            errors[`documents.${index}.type`],
                                        )}
                                    />
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor={`doc-title-${row.key}`}>
                                        Judul dokumen
                                    </Label>
                                    <Input
                                        id={`doc-title-${row.key}`}
                                        value={row.title[LOCALE] ?? ''}
                                        onChange={(event) =>
                                            patch(index, {
                                                title: {
                                                    [LOCALE]:
                                                        event.target.value,
                                                },
                                            })
                                        }
                                        placeholder="Contoh: Certificate of Analysis Batch SP-2409"
                                        aria-invalid={Boolean(titleError)}
                                    />
                                    {titleError && (
                                        <p className="text-xs text-destructive">
                                            {titleError}
                                        </p>
                                    )}
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor={`doc-batch-${row.key}`}>
                                        Referensi batch
                                    </Label>
                                    <Input
                                        id={`doc-batch-${row.key}`}
                                        dir="ltr"
                                        value={row.batch_reference}
                                        onChange={(event) =>
                                            patch(index, {
                                                batch_reference:
                                                    event.target.value,
                                            })
                                        }
                                        placeholder="Contoh: SP-2409"
                                        aria-invalid={Boolean(
                                            errors[
                                                `documents.${index}.batch_reference`
                                            ],
                                        )}
                                    />
                                </div>

                                <div className="grid gap-1.5">
                                    <Label htmlFor={`doc-issued-${row.key}`}>
                                        Tanggal terbit
                                    </Label>
                                    <DatePicker
                                        id={`doc-issued-${row.key}`}
                                        value={row.issued_at}
                                        onChange={(value) =>
                                            patch(index, { issued_at: value })
                                        }
                                        placeholder="Pilih tanggal terbit dokumen"
                                        aria-invalid={Boolean(
                                            errors[
                                                `documents.${index}.issued_at`
                                            ],
                                        )}
                                    />
                                    {errors[`documents.${index}.issued_at`] && (
                                        <p className="text-xs text-destructive">
                                            {
                                                errors[
                                                    `documents.${index}.issued_at`
                                                ]
                                            }
                                        </p>
                                    )}
                                </div>
                            </div>

                            {row.file && (
                                <div className="flex flex-wrap items-center gap-3 rounded-[var(--radius)] bg-muted/50 px-3 py-2.5">
                                    <Icon
                                        aria-hidden
                                        className="size-5 shrink-0 text-muted-foreground"
                                    />
                                    <span className="min-w-0 flex-1 truncate text-sm">
                                        {row.file.name}
                                        <span className="ms-2 text-xs text-muted-foreground tabular-nums">
                                            {formatBytes(row.file.size)}
                                        </span>
                                    </span>

                                    <Button
                                        asChild
                                        variant="detail"
                                        size="sm"
                                        type="button"
                                    >
                                        <a
                                            href={row.file.url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <Download />
                                            Unduh
                                        </a>
                                    </Button>

                                    {!showUploader && (
                                        <Button
                                            type="button"
                                            variant="upload"
                                            size="sm"
                                            onClick={() =>
                                                setReplacing((current) => [
                                                    ...current,
                                                    row.key,
                                                ])
                                            }
                                        >
                                            <Upload />
                                            Ganti Berkas
                                        </Button>
                                    )}
                                </div>
                            )}

                            {showUploader && (
                                <div className="grid gap-1.5">
                                    <Label>
                                        {row.file
                                            ? 'Berkas pengganti'
                                            : 'Berkas dokumen'}
                                    </Label>
                                    <DocumentUploader
                                        onChange={(tokens) =>
                                            patch(index, {
                                                token: tokens[0] ?? null,
                                            })
                                        }
                                    />
                                    {row.file && (
                                        <p className="text-xs text-muted-foreground">
                                            Berkas lama akan dihapus setelah
                                            perubahan disimpan.
                                        </p>
                                    )}
                                </div>
                            )}

                            <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border pt-3">
                                <label className="flex items-center gap-2 text-sm">
                                    <Switch
                                        checked={row.is_active}
                                        onCheckedChange={(checked) =>
                                            patch(index, {
                                                is_active: checked,
                                            })
                                        }
                                    />
                                    <span className="text-muted-foreground">
                                        Bisa diunduh dari website publik
                                    </span>
                                </label>

                                <Button
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    onClick={() => remove(index)}
                                >
                                    <Trash2 />
                                    Hapus Dokumen
                                </Button>
                            </div>
                        </div>
                    );
                })
            )}
        </div>
    );
}
