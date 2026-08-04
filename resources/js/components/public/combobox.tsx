import { Check, ChevronsUpDown } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ReactNode } from 'react';
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
    /** Ditampilkan redup di ujung baris — kode, ukuran, atau singkatan. */
    hint?: string;
    /** Lencana kecil di depan label, misalnya bendera negara. */
    icon?: ReactNode;
};

type Props = {
    id: string;
    /** Nama isian yang ikut terkirim bersama formulir. */
    name: string;
    options: ComboboxOption[];
    defaultValue?: string;
    /** Tulisan saat belum ada yang dipilih. */
    placeholder: string;
    /** Tulisan di dalam kotak pencarian. */
    searchPlaceholder: string;
    /** Tulisan saat pencarian tidak menemukan apa pun. */
    emptyText: string;
    className?: string;
    /** Dipanggil tiap pilihan berubah, untuk pemakai yang bukan formulir. */
    onValueChange?: (value: string) => void;
    /*
     * Disambungkan `Field` lewat `cloneElement`, jadi harus diteruskan ke
     * tombol pemicunya — bukan berhenti di sini.
     */
    'aria-invalid'?: boolean;
    'aria-describedby'?: string;
};

/**
 * Daftar pilihan yang bisa dicari.
 *
 * Dipakai menggantikan `select` bawaan pada isian yang daftarnya panjang.
 * Daftar negara berisi 250 baris dan katalog produk puluhan; pada `select`
 * bawaan satu-satunya cara mencari adalah mengetik huruf awal berulang kali,
 * dan itu gagal begitu orang mengingat "Emirat" ketimbang "Uni Emirat Arab".
 *
 * Nilainya dititipkan pada `input` tersembunyi, bukan pada state formulir.
 * Formulir ini dikirim sebagai `FormData` dari DOM, jadi nilai yang tidak punya
 * wujud di DOM tidak pernah ikut terkirim.
 */
export default function Combobox({
    id,
    name,
    options,
    defaultValue = '',
    placeholder,
    searchPlaceholder,
    emptyText,
    className,
    onValueChange,
    'aria-invalid': ariaInvalid,
    'aria-describedby': ariaDescribedBy,
}: Props) {
    const [value, setValue] = useState(defaultValue);
    const [open, setOpen] = useState(false);
    const hiddenRef = useRef<HTMLInputElement>(null);

    /*
     * `resetOnSuccess` pada `Form` Inertia mengosongkan kendali di DOM, lalu
     * melepas event `reset` pada elemen formulirnya. Nilai di sini dipegang
     * state React, jadi tanpa mendengarkan event itu pilihan lama tetap
     * tertulis di pemicu setelah permintaan terkirim — pengunjung yang mengirim
     * permintaan kedua akan melihat negara pengiriman yang pertama masih
     * terpilih, dan mengiranya sudah benar.
     */
    useEffect(() => {
        const form = hiddenRef.current?.form;

        if (!form) {
            return;
        }

        const handleReset = () => setValue(defaultValue);

        form.addEventListener('reset', handleReset);

        return () => form.removeEventListener('reset', handleReset);
    }, [defaultValue]);

    const selected = options.find((option) => option.value === value);

    return (
        <>
            <input
                ref={hiddenRef}
                type="hidden"
                name={name}
                value={value}
                readOnly
            />

            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        id={id}
                        type="button"
                        role="combobox"
                        aria-expanded={open}
                        aria-invalid={ariaInvalid}
                        aria-describedby={ariaDescribedBy}
                        className={cn(
                            'flex w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 text-start text-base shadow-xs transition-[color,box-shadow] outline-none',
                            'focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                            'aria-invalid:border-destructive aria-invalid:ring-destructive/20',
                            'md:text-sm',
                            className,
                        )}
                    >
                        {selected ? (
                            <span className="flex min-w-0 items-center gap-2">
                                {selected.icon}
                                <span className="truncate">
                                    {selected.label}
                                </span>
                            </span>
                        ) : (
                            <span className="truncate text-muted-foreground">
                                {placeholder}
                            </span>
                        )}

                        <ChevronsUpDown
                            aria-hidden
                            className="size-4 shrink-0 text-muted-foreground"
                        />
                    </button>
                </PopoverTrigger>

                <PopoverContent
                    align="start"
                    className="w-(--radix-popover-trigger-width) p-0"
                >
                    <Command
                        /*
                         * Penyaringan bawaan `cmdk` memakai skor kemiripan yang
                         * meloloskan huruf berjauhan — mengetik "uae" ikut
                         * memunculkan "Ukraina". Di sini pencocokannya cukup
                         * "mengandung", karena yang dicari orang adalah potongan
                         * nama negara, bukan singkatannya.
                         */
                        filter={(itemValue, search) =>
                            itemValue
                                .toLowerCase()
                                .includes(search.toLowerCase())
                                ? 1
                                : 0
                        }
                    >
                        <CommandInput placeholder={searchPlaceholder} />

                        <CommandList>
                            <CommandEmpty>{emptyText}</CommandEmpty>

                            <CommandGroup>
                                {options.map((option) => (
                                    <CommandItem
                                        key={option.value}
                                        /*
                                         * Kodenya ikut dicari, jadi pembeli yang
                                         * sudah hafal "AE" tidak perlu mengeja
                                         * nama negaranya.
                                         */
                                        value={`${option.label} ${option.hint ?? ''} ${option.value}`}
                                        onSelect={() => {
                                            setValue(option.value);
                                            onValueChange?.(option.value);
                                            setOpen(false);
                                        }}
                                        className="gap-2.5 py-2"
                                    >
                                        {option.icon}

                                        <span className="min-w-0 flex-1 truncate">
                                            {option.label}
                                        </span>

                                        {option.hint && (
                                            <span className="shrink-0 font-mono text-xs text-muted-foreground">
                                                {option.hint}
                                            </span>
                                        )}

                                        <Check
                                            aria-hidden
                                            className={cn(
                                                'size-4 shrink-0 text-primary',
                                                option.value === value
                                                    ? 'opacity-100'
                                                    : 'opacity-0',
                                            )}
                                        />
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </>
    );
}
