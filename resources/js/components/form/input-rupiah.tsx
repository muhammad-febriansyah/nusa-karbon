import type { ComponentProps } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

type Props = Omit<ComponentProps<'input'>, 'value' | 'onChange' | 'type'> & {
    value: number | null;
    onChange: (value: number | null) => void;
};

const format = (value: number) => new Intl.NumberFormat('id-ID').format(value);

/**
 * Rupiah tampil dengan pemisah ribuan titik, tapi yang dikirim ke server selalu
 * integer murni tanpa pemisah. Disimpan `BIGINT` tanpa desimal.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.2
 */
export default function InputRupiah({
    value,
    onChange,
    className,
    placeholder = '0',
    ...props
}: Props) {
    const display = value === null || Number.isNaN(value) ? '' : format(value);

    return (
        <div className="relative">
            <span className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm text-muted-foreground">
                Rp
            </span>
            <Input
                inputMode="numeric"
                dir="ltr"
                value={display}
                placeholder={placeholder}
                className={cn('ps-10 text-end tabular-nums', className)}
                onChange={(event) => {
                    const digits = event.target.value.replace(/\D/g, '');

                    onChange(
                        digits === '' ? null : Number.parseInt(digits, 10),
                    );
                }}
                {...props}
            />
        </div>
    );
}
