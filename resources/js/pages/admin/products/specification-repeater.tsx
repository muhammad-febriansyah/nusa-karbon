import { GripVertical, Plus, Sparkles, Star, Trash2 } from 'lucide-react';
import SortableList from '@/components/shared/sortable-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import type {
    SpecificationRow,
    SpecificationTemplateRow,
} from '@/types/models';

const LOCALE = 'id';

type Props = {
    rows: SpecificationRow[];
    onChange: (rows: SpecificationRow[]) => void;
    template: SpecificationTemplateRow[];
    /** Error validasi per baris, kunci mengikuti indeks larik. */
    errors?: Record<string, string>;
};

/**
 * Setiap baris diberi `key` lokal karena baris baru belum punya id dari
 * server, sedangkan drag-and-drop butuh identitas yang stabil.
 */
const localKey = () => `row-${Math.random().toString(36).slice(2, 10)}`;

export const emptySpecification = (): SpecificationRow => ({
    key: localKey(),
    id: null,
    parameter: { [LOCALE]: '' },
    value: '',
    unit: '',
    is_highlighted: false,
});

export const templateToRows = (
    template: SpecificationTemplateRow[],
): SpecificationRow[] =>
    template.map((item) => ({
        key: localKey(),
        id: null,
        parameter: { [LOCALE]: item.parameter },
        value: item.value,
        unit: item.unit ?? '',
        is_highlighted: item.is_highlighted,
    }));

export default function SpecificationRepeater({
    rows,
    onChange,
    template,
    errors = {},
}: Props) {
    const patch = (index: number, changes: Partial<SpecificationRow>) =>
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
                    onClick={() => onChange([...rows, emptySpecification()])}
                >
                    <Plus />
                    Tambah Baris
                </Button>

                <Button
                    type="button"
                    variant="duplicate"
                    size="sm"
                    onClick={() => onChange(templateToRows(template))}
                    disabled={rows.length > 0}
                    title={
                        rows.length > 0
                            ? 'Kosongkan dulu spesifikasi yang ada'
                            : undefined
                    }
                >
                    <Sparkles />
                    Isi dari Template
                </Button>

                {rows.length > 0 && (
                    <span className="text-xs text-muted-foreground">
                        Geser ikon <GripVertical className="inline size-3" />{' '}
                        untuk mengubah urutan tampil.
                    </span>
                )}
            </div>

            {rows.length === 0 ? (
                <p className="rounded-[var(--radius)] border border-dashed border-input px-4 py-6 text-center text-sm text-muted-foreground">
                    Belum ada spesifikasi. Pakai &ldquo;Isi dari Template&rdquo;
                    untuk memuat 9 parameter standar briket.
                </p>
            ) : (
                <SortableList
                    items={rows}
                    itemId={(row) => row.key}
                    onReorder={onChange}
                >
                    {(row, index) => (
                        <div className="grid gap-3 sm:grid-cols-[1.4fr_1fr_0.8fr_auto]">
                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor={`spec-parameter-${row.key}`}
                                    className="sm:sr-only"
                                >
                                    Parameter
                                </Label>
                                <Input
                                    id={`spec-parameter-${row.key}`}
                                    value={row.parameter[LOCALE] ?? ''}
                                    onChange={(event) =>
                                        patch(index, {
                                            parameter: {
                                                [LOCALE]: event.target.value,
                                            },
                                        })
                                    }
                                    placeholder="Contoh: Fixed Carbon"
                                    aria-invalid={Boolean(
                                        errors[
                                            `specifications.${index}.parameter.${LOCALE}`
                                        ],
                                    )}
                                />
                                {errors[
                                    `specifications.${index}.parameter.${LOCALE}`
                                ] && (
                                    <p className="text-xs text-destructive">
                                        {
                                            errors[
                                                `specifications.${index}.parameter.${LOCALE}`
                                            ]
                                        }
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor={`spec-value-${row.key}`}
                                    className="sm:sr-only"
                                >
                                    Nilai
                                </Label>
                                <Input
                                    id={`spec-value-${row.key}`}
                                    dir="ltr"
                                    value={row.value}
                                    onChange={(event) =>
                                        patch(index, {
                                            value: event.target.value,
                                        })
                                    }
                                    placeholder="Contoh: 80"
                                    aria-invalid={Boolean(
                                        errors[`specifications.${index}.value`],
                                    )}
                                />
                                {errors[`specifications.${index}.value`] && (
                                    <p className="text-xs text-destructive">
                                        {
                                            errors[
                                                `specifications.${index}.value`
                                            ]
                                        }
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-1.5">
                                <Label
                                    htmlFor={`spec-unit-${row.key}`}
                                    className="sm:sr-only"
                                >
                                    Satuan
                                </Label>
                                <Input
                                    id={`spec-unit-${row.key}`}
                                    dir="ltr"
                                    value={row.unit}
                                    onChange={(event) =>
                                        patch(index, {
                                            unit: event.target.value,
                                        })
                                    }
                                    placeholder="Contoh: % min"
                                />
                            </div>

                            <div className="flex items-center gap-2">
                                <label
                                    className="flex items-center gap-1.5 text-xs text-muted-foreground"
                                    title="Spesifikasi yang disorot tampil lebih menonjol di halaman produk"
                                >
                                    <Star aria-hidden className="size-3.5" />
                                    <span className="sr-only sm:not-sr-only">
                                        Sorot
                                    </span>
                                    <Switch
                                        checked={row.is_highlighted}
                                        onCheckedChange={(checked) =>
                                            patch(index, {
                                                is_highlighted: checked,
                                            })
                                        }
                                    />
                                </label>

                                <Button
                                    type="button"
                                    variant="delete"
                                    size="sm"
                                    onClick={() => remove(index)}
                                >
                                    <Trash2 />
                                    <span className="sr-only">
                                        Hapus baris spesifikasi
                                    </span>
                                </Button>
                            </div>
                        </div>
                    )}
                </SortableList>
            )}
        </div>
    );
}
