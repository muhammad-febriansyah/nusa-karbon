import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginatedMeta } from '@/types/pagination';

const PER_PAGE_OPTIONS = [10, 15, 25, 50, 100];

type Props = {
    meta: PaginatedMeta;
    onPageChange: (page: number) => void;
    onPerPageChange: (perPage: number) => void;
    selectedCount?: number;
};

export function DataTablePagination({
    meta,
    onPageChange,
    onPerPageChange,
    selectedCount = 0,
}: Props) {
    const from = meta.from ?? 0;
    const to = meta.to ?? 0;

    return (
        <div className="flex flex-col items-center justify-between gap-3 border-t border-border px-6 py-3.5 sm:flex-row">
            <p className="text-[13px] text-muted-foreground">
                {selectedCount > 0 ? (
                    <>
                        <span className="tabular-nums">{selectedCount}</span>{' '}
                        baris terpilih dari{' '}
                        <span className="tabular-nums">{meta.total}</span>
                    </>
                ) : (
                    <>
                        Menampilkan <span className="tabular-nums">{from}</span>
                        –<span className="tabular-nums">{to}</span> dari{' '}
                        <span className="tabular-nums">{meta.total}</span> data
                    </>
                )}
            </p>

            <div className="flex items-center gap-4">
                <label className="flex items-center gap-2 text-[13px] text-muted-foreground">
                    Baris
                    <Select
                        value={String(meta.per_page)}
                        onValueChange={(value) =>
                            onPerPageChange(Number(value))
                        }
                    >
                        <SelectTrigger size="sm" className="w-18">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {PER_PAGE_OPTIONS.map((option) => (
                                <SelectItem key={option} value={String(option)}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </label>

                <div className="flex items-center gap-2">
                    <span className="text-[13px] text-muted-foreground tabular-nums">
                        Hal. {meta.current_page} / {meta.last_page}
                    </span>

                    <Button
                        type="button"
                        variant="back"
                        size="sm"
                        disabled={meta.current_page <= 1}
                        onClick={() => onPageChange(meta.current_page - 1)}
                    >
                        <ChevronLeft className="rtl:rotate-180" />
                        Sebelumnya
                    </Button>

                    <Button
                        type="button"
                        variant="back"
                        size="sm"
                        disabled={meta.current_page >= meta.last_page}
                        onClick={() => onPageChange(meta.current_page + 1)}
                    >
                        Berikutnya
                        <ChevronRight className="rtl:rotate-180" />
                    </Button>
                </div>
            </div>
        </div>
    );
}
