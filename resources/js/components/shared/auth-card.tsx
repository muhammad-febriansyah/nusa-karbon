import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

/** Permukaan putih yang membungkus form auth. */
export default function AuthCard({
    children,
    className,
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'rounded-card flex flex-col gap-4 border border-border bg-card p-6',
                className,
            )}
        >
            {children}
        </div>
    );
}
