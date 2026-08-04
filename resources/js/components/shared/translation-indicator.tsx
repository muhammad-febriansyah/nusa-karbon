import { cn } from '@/lib/utils';

export type TranslationStatus = 'complete' | 'partial' | 'empty';

export type TranslationCompleteness = Record<string, TranslationStatus>;

const dotClasses: Record<TranslationStatus, string> = {
    complete: 'bg-success',
    partial: 'bg-warning',
    empty: 'bg-border',
};

const statusLabel: Record<TranslationStatus, string> = {
    complete: 'lengkap',
    partial: 'sebagian',
    empty: 'kosong',
};

/**
 * Tiga titik berlabel EN / AR / RU. Muncul di kolom tabel dan di sisi tab
 * bahasa pada form.
 *
 * @see docs/05-design-system.md bagian 9
 */
export default function TranslationIndicator({
    completeness,
    showLabels = true,
    className,
}: {
    completeness: TranslationCompleteness;
    showLabels?: boolean;
    className?: string;
}) {
    return (
        <span className={cn('inline-flex items-center gap-2', className)}>
            {Object.entries(completeness).map(([locale, status]) => (
                <span
                    key={locale}
                    className="inline-flex items-center gap-1"
                    title={`${locale.toUpperCase()}: terjemahan ${statusLabel[status]}`}
                >
                    <span
                        aria-hidden
                        className={cn(
                            'size-1.5 rounded-full',
                            dotClasses[status],
                        )}
                    />
                    {showLabels && (
                        <span
                            dir="ltr"
                            className="text-[10px] font-medium tracking-wide text-muted-foreground uppercase"
                        >
                            {locale}
                        </span>
                    )}
                    <span className="sr-only">
                        {locale.toUpperCase()}: terjemahan {statusLabel[status]}
                    </span>
                </span>
            ))}
        </span>
    );
}
