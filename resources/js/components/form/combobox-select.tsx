import { Check, ChevronsUpDown, Loader2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

export type ComboboxOption = {
    value: string;
    label: string;
    disabled?: boolean;
};

type BaseProps = {
    options: ComboboxOption[];
    placeholder?: string;
    searchPlaceholder?: string;
    disabled?: boolean;
    loading?: boolean;
    clearable?: boolean;
    id?: string;
    'aria-describedby'?: string;
    'aria-invalid'?: boolean;
};

type SingleProps = BaseProps & {
    multiple?: false;
    value: string | null;
    onChange: (value: string | null) => void;
};

type MultipleProps = BaseProps & {
    multiple: true;
    value: string[];
    onChange: (value: string[]) => void;
};

type Props = SingleProps | MultipleProps;

/**
 * Select searchable pola select2. Wajib dipakai bila opsi lebih dari 10.
 * Untuk daftar sangat panjang, `CommandList` sudah bergulir sendiri sehingga
 * hanya sebagian opsi yang terlihat.
 *
 * @see docs/07-admin-ui-standards.md bagian 5.4
 */
export default function ComboboxSelect(props: Props) {
    const {
        options,
        placeholder = 'Pilih…',
        searchPlaceholder = 'Ketik untuk mencari…',
        disabled,
        loading = false,
        clearable = true,
        id,
    } = props;

    // Tidak memakai rest spread: `value`, `onChange`, dan `multiple` tidak
    // boleh ikut menempel ke elemen <button>.
    const describedBy = props['aria-describedby'];
    const invalid = props['aria-invalid'];

    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');

    const selectedValues = useMemo(
        () =>
            props.multiple
                ? props.value
                : props.value !== null
                  ? [props.value]
                  : [],
        [props.multiple, props.value],
    );

    const selectedOptions = options.filter((option) =>
        selectedValues.includes(option.value),
    );

    const toggle = (value: string) => {
        if (props.multiple) {
            const next = props.value.includes(value)
                ? props.value.filter((item) => item !== value)
                : [...props.value, value];

            props.onChange(next);

            return;
        }

        props.onChange(props.value === value ? null : value);
        setOpen(false);
    };

    const clear = () => {
        if (props.multiple) {
            props.onChange([]);

            return;
        }

        props.onChange(null);
    };

    const hasValue = selectedValues.length > 0;

    return (
        <div className="relative">
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        id={id}
                        role="combobox"
                        aria-expanded={open}
                        disabled={disabled || loading}
                        aria-describedby={describedBy}
                        aria-invalid={invalid}
                        className={cn(
                            'flex min-h-9 w-full items-center gap-2 rounded-[var(--radius)] border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none',
                            'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                            'aria-invalid:border-destructive aria-invalid:ring-destructive/20',
                            'disabled:pointer-events-none disabled:opacity-50',
                        )}
                    >
                        <span className="flex flex-1 flex-wrap items-center gap-1 text-start">
                            {!hasValue && (
                                <span className="text-muted-foreground">
                                    {placeholder}
                                </span>
                            )}

                            {props.multiple
                                ? selectedOptions.map((option) => (
                                      <Badge
                                          key={option.value}
                                          className="gap-1 bg-muted text-foreground"
                                      >
                                          {option.label}
                                          <span
                                              role="button"
                                              tabIndex={-1}
                                              aria-label={`Hapus ${option.label}`}
                                              onClick={(event) => {
                                                  event.stopPropagation();
                                                  toggle(option.value);
                                              }}
                                              className="cursor-pointer opacity-60 hover:opacity-100"
                                          >
                                              <X className="size-3" />
                                          </span>
                                      </Badge>
                                  ))
                                : selectedOptions[0]?.label}
                        </span>

                        {loading ? (
                            <Loader2
                                aria-hidden
                                className="size-4 shrink-0 animate-spin text-muted-foreground"
                            />
                        ) : (
                            <ChevronsUpDown
                                aria-hidden
                                className="size-4 shrink-0 text-muted-foreground"
                            />
                        )}
                    </button>
                </PopoverTrigger>

                <PopoverContent
                    align="start"
                    className="w-(--radix-popover-trigger-width) p-0"
                >
                    <Command shouldFilter>
                        <CommandInput
                            value={query}
                            onValueChange={setQuery}
                            placeholder={searchPlaceholder}
                        />
                        <CommandList>
                            <CommandEmpty>
                                Tidak ada hasil untuk &ldquo;{query}&rdquo;
                            </CommandEmpty>
                            <CommandGroup>
                                {clearable && hasValue && (
                                    <CommandItem
                                        value="__clear__"
                                        onSelect={() => {
                                            clear();
                                            setOpen(false);
                                        }}
                                        className="text-muted-foreground"
                                    >
                                        <X className="size-4" />
                                        Hapus pilihan
                                    </CommandItem>
                                )}

                                {options.map((option) => (
                                    <CommandItem
                                        key={option.value}
                                        value={option.label}
                                        disabled={option.disabled}
                                        onSelect={() => toggle(option.value)}
                                    >
                                        <Check
                                            className={cn(
                                                'size-4',
                                                selectedValues.includes(
                                                    option.value,
                                                )
                                                    ? 'opacity-100'
                                                    : 'opacity-0',
                                            )}
                                        />
                                        {option.label}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    );
}
