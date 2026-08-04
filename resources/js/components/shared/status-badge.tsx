import { cn } from '@/lib/utils';

/**
 * Token warna yang dikembalikan `color()` pada Enum PHP.
 *
 * @see app/Enums/RfqStatus.php
 */
export type StatusTone =
    | 'primary'
    | 'success'
    | 'warning'
    | 'info'
    | 'neutral'
    | 'destructive'
    | 'muted';

/**
 * Latar beropacity rendah dengan teks berwarna penuh, supaya tetap terbaca
 * setelah tema diganti dan tidak tertukar dengan tombol.
 *
 * @see docs/05-design-system.md bagian 8
 */
const toneClasses: Record<StatusTone, string> = {
    primary: 'bg-primary/12 text-primary',
    success: 'bg-success/12 text-success',
    warning: 'bg-warning/12 text-warning',
    info: 'bg-info/12 text-info',
    neutral: 'bg-neutral/12 text-neutral',
    destructive: 'bg-destructive/12 text-destructive',
    muted: 'bg-muted text-muted-foreground',
};

type Props = {
    label: string;
    tone?: StatusTone;
    className?: string;
};

export default function StatusBadge({
    label,
    tone = 'muted',
    className,
}: Props) {
    return (
        <span
            className={cn(
                'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium whitespace-nowrap',
                toneClasses[tone],
                className,
            )}
        >
            {label}
        </span>
    );
}
