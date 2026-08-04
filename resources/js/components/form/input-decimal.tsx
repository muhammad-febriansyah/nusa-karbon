import type { ComponentProps } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

type Props = Omit<ComponentProps<'input'>, 'value' | 'onChange' | 'type'> & {
    value: number | null;
    onChange: (value: number | null) => void;
    /** Jumlah angka desimal maksimal. Berat 3, CBM 4. */
    precision?: number;
    /** Satuan yang ditampilkan di sisi akhir, misalnya "kg" atau "CBM". */
    unit?: string;
};

const toDisplay = (value: number | null) =>
    value === null || Number.isNaN(value)
        ? ''
        : String(value).replace('.', ',');

/**
 * Admin Indonesia mengetik desimal dengan koma, tapi server selalu menerima
 * titik.
 *
 * @see docs/06-architecture.md bagian 8
 */
export default function InputDecimal({
    value,
    onChange,
    precision = 3,
    unit,
    className,
    ...props
}: Props) {
    const [draft, setDraft] = useState(() => toDisplay(value));

    // Draft dipakai selama masih mewakili nilai yang sama, supaya "1," dan
    // "1,50" tidak dinormalkan saat diketik. Bila nilai diubah dari luar
    // (misalnya reset form), tampilan langsung mengikuti nilai itu — dihitung
    // saat render, bukan lewat effect.
    const parsedDraft = draft === '' ? null : Number(draft.replace(',', '.'));
    const display = parsedDraft === value ? draft : toDisplay(value);

    return (
        <div className="relative">
            <Input
                inputMode="decimal"
                dir="ltr"
                value={display}
                className={cn(
                    'text-end tabular-nums',
                    unit && 'pe-14',
                    className,
                )}
                onChange={(event) => {
                    // Hanya angka dan satu pemisah desimal.
                    const cleaned = event.target.value
                        .replace(/[^\d,.]/g, '')
                        .replace('.', ',')
                        .replace(/,(?=.*,)/g, '');

                    const [whole, fraction] = cleaned.split(',');
                    const limited =
                        fraction === undefined
                            ? whole
                            : `${whole},${fraction.slice(0, precision)}`;

                    setDraft(limited);

                    if (limited === '' || limited === ',') {
                        onChange(null);

                        return;
                    }

                    const parsed = Number(limited.replace(',', '.'));

                    onChange(Number.isNaN(parsed) ? null : parsed);
                }}
                {...props}
            />

            {unit && (
                <span className="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-sm text-muted-foreground">
                    {unit}
                </span>
            )}
        </div>
    );
}
