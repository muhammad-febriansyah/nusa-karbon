import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

type Props = {
    title?: string;
    description?: string;
    action?: ReactNode;
    children: ReactNode;
    className?: string;
    bodyClassName?: string;
};

/**
 * Card admin: pemisahan lewat border, bukan shadow.
 * Referensi: docs/05-design-system.md bagian 5 & 6.
 */
export default function Panel({
    title,
    description,
    action,
    children,
    className,
    bodyClassName,
}: Props) {
    return (
        <section
            className={cn(
                'rounded-card overflow-hidden border border-border bg-card',
                className,
            )}
        >
            {title && (
                <header className="flex items-center justify-between gap-4 border-b border-border px-6 py-4">
                    <div className="min-w-0 space-y-0.5">
                        <h2 className="text-base font-semibold">{title}</h2>
                        {description && (
                            <p className="text-[13px] text-muted-foreground">
                                {description}
                            </p>
                        )}
                    </div>
                    {action && <div className="shrink-0">{action}</div>}
                </header>
            )}

            <div className={cn('p-6', bodyClassName)}>{children}</div>
        </section>
    );
}
