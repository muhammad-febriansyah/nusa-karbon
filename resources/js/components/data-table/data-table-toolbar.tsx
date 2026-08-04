import { Columns3, RotateCcw, Search } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';

export type ToggleableColumn = {
    id: string;
    label: string;
    visible: boolean;
    /** Kolom yang tidak boleh disembunyikan, misalnya kolom aksi. */
    locked?: boolean;
};

type Props = {
    search: string;
    onSearchChange: (value: string) => void;
    searchPlaceholder?: string;
    columns: ToggleableColumn[];
    onToggleColumn: (id: string) => void;
    /** Filter khusus modul, dirender di antara pencarian dan pemilih kolom. */
    filters?: ReactNode;
    /** Aksi massal, hanya tampil saat ada baris terpilih. */
    bulkActions?: ReactNode;
    selectedCount?: number;
    canReset: boolean;
    onReset: () => void;
};

export function DataTableToolbar({
    search,
    onSearchChange,
    searchPlaceholder = 'Cari…',
    columns,
    onToggleColumn,
    filters,
    bulkActions,
    selectedCount = 0,
    canReset,
    onReset,
}: Props) {
    return (
        <div className="flex flex-col gap-3 border-b border-border px-6 py-3.5">
            <div className="flex flex-wrap items-center gap-2">
                <div className="relative flex min-w-56 flex-1 items-center">
                    <Search
                        aria-hidden
                        className="absolute start-3 size-4 text-muted-foreground"
                    />
                    <Input
                        type="search"
                        value={search}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder={searchPlaceholder}
                        aria-label="Cari"
                        className="ps-9"
                    />
                </div>

                {filters}

                {canReset && (
                    <Button
                        type="button"
                        variant="reset"
                        size="sm"
                        onClick={onReset}
                    >
                        <RotateCcw />
                        Reset Filter
                    </Button>
                )}

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button type="button" variant="filter" size="sm">
                            <Columns3 />
                            Kolom
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-48">
                        <DropdownMenuLabel>Tampilkan kolom</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        {columns
                            .filter((column) => !column.locked)
                            .map((column) => (
                                <DropdownMenuCheckboxItem
                                    key={column.id}
                                    checked={column.visible}
                                    onCheckedChange={() =>
                                        onToggleColumn(column.id)
                                    }
                                    onSelect={(event) => event.preventDefault()}
                                >
                                    {column.label}
                                </DropdownMenuCheckboxItem>
                            ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            {selectedCount > 0 && bulkActions && (
                <div className="flex flex-wrap items-center gap-2 rounded-[var(--radius)] bg-muted px-3 py-2">
                    <span className="text-[13px] font-medium">
                        <span className="tabular-nums">{selectedCount}</span>{' '}
                        baris terpilih
                    </span>
                    <span className="flex flex-wrap items-center gap-2">
                        {bulkActions}
                    </span>
                </div>
            )}
        </div>
    );
}
