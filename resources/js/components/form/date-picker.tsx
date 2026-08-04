import { format } from 'date-fns';
import { id as idLocale } from 'date-fns/locale';
import { CalendarIcon, X } from 'lucide-react';
import { useState } from 'react';
import { Calendar } from '@/components/ui/calendar';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

type Props = {
    /** Tanggal ISO `yyyy-MM-dd`, atau null. */
    value: string | null;
    onChange: (value: string | null) => void;
    placeholder?: string;
    disabled?: boolean;
    clearable?: boolean;
    id?: string;
    'aria-describedby'?: string;
    'aria-invalid'?: boolean;
};

const DISPLAY_FORMAT = 'dd MMMM yyyy';

/**
 * Dilarang memakai `<input type="date">` mentah.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.3
 */
export default function DatePicker({
    value,
    onChange,
    placeholder = 'Pilih tanggal',
    disabled,
    clearable = true,
    id,
    ...aria
}: Props) {
    const [open, setOpen] = useState(false);

    const selected = value ? new Date(`${value}T00:00:00`) : undefined;

    return (
        <div className="relative">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        id={id}
                        disabled={disabled}
                        {...aria}
                        className={cn(
                            'flex h-9 w-full items-center gap-2 rounded-[var(--radius)] border border-input bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                            'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                            'aria-invalid:border-destructive aria-invalid:ring-destructive/20',
                            'disabled:pointer-events-none disabled:opacity-50',
                            clearable && value && 'pe-9',
                        )}
                    >
                        <CalendarIcon
                            aria-hidden
                            className="size-4 shrink-0 text-muted-foreground"
                        />
                        <span
                            className={cn(
                                'truncate',
                                !selected && 'text-muted-foreground',
                            )}
                        >
                            {selected
                                ? format(selected, DISPLAY_FORMAT, {
                                      locale: idLocale,
                                  })
                                : placeholder}
                        </span>
                    </button>
                </PopoverTrigger>

                <PopoverContent align="start" className="w-auto p-0">
                    <Calendar
                        mode="single"
                        locale={idLocale}
                        selected={selected}
                        defaultMonth={selected}
                        onSelect={(date) => {
                            onChange(date ? format(date, 'yyyy-MM-dd') : null);
                            setOpen(false);
                        }}
                    />
                </PopoverContent>
            </Popover>

            {clearable && value && !disabled && (
                <button
                    type="button"
                    onClick={() => onChange(null)}
                    className="absolute inset-y-0 end-0 flex items-center pe-3 text-muted-foreground hover:text-foreground"
                >
                    <X className="size-4" />
                    <span className="sr-only">Hapus pilihan tanggal</span>
                </button>
            )}
        </div>
    );
}
