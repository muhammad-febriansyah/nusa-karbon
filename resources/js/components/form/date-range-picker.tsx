import { endOfMonth, format, startOfMonth, subDays, subMonths } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import { CalendarRange, X } from 'lucide-react';
import { useState } from 'react';
import type { DateRange } from 'react-day-picker';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type DateRangeValue = {
    from: string | null;
    to: string | null;
};

type Props = {
    value: DateRangeValue;
    onChange: (value: DateRangeValue) => void;
    placeholder?: string;
    disabled?: boolean;
    id?: string;
};

const DISPLAY_FORMAT = 'dd MMM yyyy';
const toIso = (date: Date) => format(date, 'yyyy-MM-dd');

/**
 * Preset rentang untuk filter RFQ dan log aktivitas.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.3
 */
const presets: Array<{ label: string; range: () => DateRangeValue }> = [
    {
        label: 'Hari ini',
        range: () => ({ from: toIso(new Date()), to: toIso(new Date()) }),
    },
    {
        label: '7 hari terakhir',
        range: () => ({
            from: toIso(subDays(new Date(), 6)),
            to: toIso(new Date()),
        }),
    },
    {
        label: '30 hari terakhir',
        range: () => ({
            from: toIso(subDays(new Date(), 29)),
            to: toIso(new Date()),
        }),
    },
    {
        label: 'Bulan ini',
        range: () => ({
            from: toIso(startOfMonth(new Date())),
            to: toIso(endOfMonth(new Date())),
        }),
    },
    {
        label: 'Bulan lalu',
        range: () => {
            const previous = subMonths(new Date(), 1);

            return {
                from: toIso(startOfMonth(previous)),
                to: toIso(endOfMonth(previous)),
            };
        },
    },
];

export default function DateRangePicker({
    value,
    onChange,
    placeholder = 'Pilih rentang tanggal',
    disabled,
    id,
}: Props) {
    const [open, setOpen] = useState(false);

    const selected: DateRange | undefined = value.from
        ? {
              from: new Date(`${value.from}T00:00:00`),
              to: value.to ? new Date(`${value.to}T00:00:00`) : undefined,
          }
        : undefined;

    const label = selected?.from
        ? selected.to
            ? `${format(selected.from, DISPLAY_FORMAT, { locale: idLocale })} – ${format(selected.to, DISPLAY_FORMAT, { locale: idLocale })}`
            : format(selected.from, DISPLAY_FORMAT, { locale: idLocale })
        : placeholder;

    const hasValue = value.from !== null || value.to !== null;

    return (
        <div className="relative">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        id={id}
                        disabled={disabled}
                        className={cn(
                            'flex h-9 w-full items-center gap-2 rounded-[var(--radius)] border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                            'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                            'disabled:pointer-events-none disabled:opacity-50',
                            hasValue && 'pe-9',
                        )}
                    >
                        <CalendarRange
                            aria-hidden
                            className="size-4 shrink-0 text-muted-foreground"
                        />
                        <span
                            className={cn(
                                'truncate',
                                !hasValue && 'text-muted-foreground',
                            )}
                        >
                            {label}
                        </span>
                    </button>
                </PopoverTrigger>

                <PopoverContent align="start" className="w-auto p-0">
                    <div className="flex flex-col sm:flex-row">
                        <div className="flex flex-col gap-1 border-b border-border p-2 sm:border-e sm:border-b-0">
                            {presets.map((preset) => (
                                <Button
                                    key={preset.label}
                                    type="button"
                                    variant="cancel"
                                    size="sm"
                                    className="justify-start"
                                    onClick={() => {
                                        onChange(preset.range());
                                        setOpen(false);
                                    }}
                                >
                                    {preset.label}
                                </Button>
                            ))}
                        </div>

                        <Calendar
                            mode="range"
                            numberOfMonths={2}
                            locale={idLocale}
                            selected={selected}
                            defaultMonth={selected?.from}
                            onSelect={(range) =>
                                onChange({
                                    from: range?.from
                                        ? toIso(range.from)
                                        : null,
                                    to: range?.to ? toIso(range.to) : null,
                                })
                            }
                        />
                    </div>
                </PopoverContent>
            </Popover>

            {hasValue && !disabled && (
                <button
                    type="button"
                    onClick={() => onChange({ from: null, to: null })}
                    className="absolute inset-y-0 end-0 flex items-center pe-3 text-muted-foreground hover:text-foreground"
                >
                    <X className="size-4" />
                    <span className="sr-only">Hapus rentang tanggal</span>
                </button>
            )}
        </div>
    );
}
