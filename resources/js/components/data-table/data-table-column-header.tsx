import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-react';
import { cn } from '@/lib/utils';

type Props = {
    title: string;
    /** Kunci kolom di server. Kosongkan bila kolom tidak bisa diurutkan. */
    column?: string;
    activeColumn?: string;
    direction?: 'asc' | 'desc';
    onSort?: (column: string) => void;
    className?: string;
};

export function DataTableColumnHeader({
    title,
    column,
    activeColumn,
    direction,
    onSort,
    className,
}: Props) {
    if (!column || !onSort) {
        return <span className={className}>{title}</span>;
    }

    const isActive = activeColumn === column;
    const Icon = !isActive
        ? ChevronsUpDown
        : direction === 'asc'
          ? ArrowUp
          : ArrowDown;

    return (
        <button
            type="button"
            onClick={() => onSort(column)}
            aria-sort={
                isActive
                    ? direction === 'asc'
                        ? 'ascending'
                        : 'descending'
                    : 'none'
            }
            className={cn(
                'inline-flex items-center gap-1.5 rounded-[var(--radius)] transition-colors outline-none hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring',
                isActive && 'text-foreground',
                className,
            )}
        >
            {title}
            <Icon aria-hidden className="size-3.5 shrink-0" />
        </button>
    );
}
