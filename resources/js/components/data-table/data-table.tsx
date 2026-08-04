import type { ReactNode } from 'react';
import { useMemo, useState } from 'react';
import EmptyState from '@/components/shared/empty-state';
import { Checkbox } from '@/components/ui/checkbox';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTableFilters } from '@/hooks/use-table-filters';
import { cn } from '@/lib/utils';
import type { Paginated, TableFilters } from '@/types/pagination';
import { DataTableColumnHeader } from './data-table-column-header';
import { DataTablePagination } from './data-table-pagination';
import type { ToggleableColumn } from './data-table-toolbar';
import { DataTableToolbar } from './data-table-toolbar';

export type DataTableColumn<TRow> = {
    id: string;
    header: string;
    /** Kunci kolom di server. Kosongkan bila tidak bisa diurutkan. */
    sortKey?: string;
    cell: (row: TRow) => ReactNode;
    /** Kolom aksi: sticky di sisi akhir dan tidak bisa disembunyikan. */
    sticky?: boolean;
    align?: 'start' | 'end';
    className?: string;
    /** Sembunyikan secara bawaan; tetap bisa dimunculkan lewat pemilih kolom. */
    hiddenByDefault?: boolean;
};

type Props<TRow> = {
    rows: Paginated<TRow>;
    columns: DataTableColumn<TRow>[];
    filters: TableFilters;
    /** Pengenal unik baris, dipakai untuk key React dan bulk selection. */
    rowId: (row: TRow) => string | number;
    searchPlaceholder?: string;
    /** Filter khusus modul untuk toolbar. */
    filterSlot?: ReactNode;
    /** Aksi massal; menerima daftar id terpilih. */
    bulkActions?: (selected: Array<string | number>) => ReactNode;
    emptyTitle?: string;
    emptyDescription?: string;
    emptyAction?: ReactNode;
    /** Nama prop Inertia yang di-reload saat filter berubah. */
    reloadOnly?: string[];
    loading?: boolean;
};

export function DataTable<TRow>({
    rows,
    columns,
    filters,
    rowId,
    searchPlaceholder,
    filterSlot,
    bulkActions,
    emptyTitle,
    emptyDescription,
    emptyAction,
    reloadOnly = [],
    loading = false,
}: Props<TRow>) {
    const {
        search,
        setSearch,
        setSort,
        setPage,
        setPerPage,
        reset,
        hasActiveFilters,
    } = useTableFilters(filters, reloadOnly);

    const [hidden, setHidden] = useState<Set<string>>(
        () =>
            new Set(
                columns
                    .filter((column) => column.hiddenByDefault)
                    .map((column) => column.id),
            ),
    );
    const [selected, setSelected] = useState<Set<string | number>>(new Set());

    const visibleColumns = useMemo(
        () => columns.filter((column) => !hidden.has(column.id)),
        [columns, hidden],
    );

    const toggleable: ToggleableColumn[] = columns.map((column) => ({
        id: column.id,
        label: column.header,
        visible: !hidden.has(column.id),
        locked: column.sticky,
    }));

    const selectable = Boolean(bulkActions);
    const pageIds = rows.data.map(rowId);
    const allOnPageSelected =
        pageIds.length > 0 && pageIds.every((id) => selected.has(id));

    const toggleColumn = (id: string) => {
        setHidden((current) => {
            const next = new Set(current);

            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }

            return next;
        });
    };

    const toggleRow = (id: string | number) => {
        setSelected((current) => {
            const next = new Set(current);

            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }

            return next;
        });
    };

    const togglePage = () => {
        setSelected((current) => {
            const next = new Set(current);
            pageIds.forEach((id) =>
                allOnPageSelected ? next.delete(id) : next.add(id),
            );

            return next;
        });
    };

    const columnCount = visibleColumns.length + (selectable ? 1 : 0);

    return (
        <div className="rounded-card overflow-hidden border border-border bg-card">
            <DataTableToolbar
                search={search}
                onSearchChange={setSearch}
                searchPlaceholder={searchPlaceholder}
                columns={toggleable}
                onToggleColumn={toggleColumn}
                filters={filterSlot}
                bulkActions={bulkActions?.([...selected])}
                selectedCount={selected.size}
                canReset={hasActiveFilters || search !== ''}
                onReset={() => {
                    setSelected(new Set());
                    reset();
                }}
            />

            <div className="overflow-x-auto">
                <Table>
                    <TableHeader>
                        <TableRow>
                            {selectable && (
                                <TableHead className="w-10 ps-6">
                                    <Checkbox
                                        checked={allOnPageSelected}
                                        onCheckedChange={togglePage}
                                        aria-label="Pilih semua baris di halaman ini"
                                    />
                                </TableHead>
                            )}

                            {visibleColumns.map((column, index) => (
                                <TableHead
                                    key={column.id}
                                    className={cn(
                                        'text-xs font-semibold tracking-wide text-muted-foreground uppercase',
                                        index === 0 && !selectable && 'ps-6',
                                        column.align === 'end' && 'text-end',
                                        column.sticky &&
                                            'sticky end-0 bg-background pe-6 text-end',
                                        column.className,
                                    )}
                                >
                                    <DataTableColumnHeader
                                        title={column.header}
                                        column={column.sortKey}
                                        activeColumn={filters.sort_by}
                                        direction={filters.sort_dir}
                                        onSort={setSort}
                                    />
                                </TableHead>
                            ))}
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        {loading ? (
                            // Skeleton sebanyak perPage, bukan spinner.
                            Array.from({ length: rows.meta.per_page }).map(
                                (_, rowIndex) => (
                                    <TableRow key={`skeleton-${rowIndex}`}>
                                        {Array.from({
                                            length: columnCount,
                                        }).map((__, cellIndex) => (
                                            <TableCell
                                                key={`skeleton-${rowIndex}-${cellIndex}`}
                                                className="h-13"
                                            >
                                                <Skeleton className="h-4 w-full" />
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ),
                            )
                        ) : rows.data.length === 0 ? (
                            <TableRow className="hover:bg-transparent">
                                <TableCell colSpan={columnCount}>
                                    <EmptyState
                                        variant={
                                            hasActiveFilters || search !== ''
                                                ? 'filtered'
                                                : 'empty'
                                        }
                                        title={emptyTitle}
                                        description={emptyDescription}
                                        action={
                                            hasActiveFilters || search !== ''
                                                ? undefined
                                                : emptyAction
                                        }
                                    />
                                </TableCell>
                            </TableRow>
                        ) : (
                            rows.data.map((row) => {
                                const id = rowId(row);

                                return (
                                    <TableRow
                                        key={id}
                                        data-state={
                                            selected.has(id)
                                                ? 'selected'
                                                : undefined
                                        }
                                        className="h-13"
                                    >
                                        {selectable && (
                                            <TableCell className="ps-6">
                                                <Checkbox
                                                    checked={selected.has(id)}
                                                    onCheckedChange={() =>
                                                        toggleRow(id)
                                                    }
                                                    aria-label="Pilih baris"
                                                />
                                            </TableCell>
                                        )}

                                        {visibleColumns.map((column, index) => (
                                            <TableCell
                                                key={column.id}
                                                className={cn(
                                                    'text-sm',
                                                    index === 0 &&
                                                        !selectable &&
                                                        'ps-6',
                                                    column.align === 'end' &&
                                                        'text-end',
                                                    column.sticky &&
                                                        'sticky end-0 bg-card pe-6 text-end',
                                                    column.className,
                                                )}
                                            >
                                                {column.cell(row)}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                );
                            })
                        )}
                    </TableBody>
                </Table>
            </div>

            <DataTablePagination
                meta={rows.meta}
                onPageChange={setPage}
                onPerPageChange={setPerPage}
                selectedCount={selected.size}
            />
        </div>
    );
}
