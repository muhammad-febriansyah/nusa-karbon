import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

export type FilterOption = {
    value: string;
    /** Termasuk jumlah isinya, misal "Panduan Ekspor (5)". */
    label: string;
};

type Props = {
    id: string;
    /** Ditulis redup di depan nilainya: "Kategori │ Semua artikel". */
    label: string;
    /** Nilai aktif; string kosong berarti tanpa saringan. */
    value: string;
    options: FilterOption[];
    /**
     * Label pilihan "tanpa saringan". Dikosongkan bila saringannya wajib
     * bernilai — misalnya urutan, yang selalu punya nilai bawaan.
     */
    resetLabel?: string;
    onChange: (value: string) => void;
};

/**
 * Nilai pengganti untuk "tanpa saringan".
 *
 * Radix memakai string kosong sebagai penanda internal "belum ada pilihan",
 * jadi pilihan pertama tidak boleh bernilai `''`. Dipilih bentuk yang tidak
 * mungkin bertabrakan dengan slug kategori mana pun.
 */
const NO_FILTER = '__semua__';

/**
 * Baris pilihan dibuat tenang, bukan memakai warna aksen bawaan komponen.
 *
 * Aksen tema ini hijau kehitaman; sebagai blok penuh di belakang satu baris
 * teks, warnanya membanjiri panel dan terbaca seperti berasal dari aplikasi
 * lain. Baris yang sedang disorot cukup ditandai latar redup, dan yang sedang
 * dipakai ditandai centang plus huruf tebal — dua penanda, bukan hanya warna.
 */
const itemClass =
    'rounded-lg py-2 focus:bg-muted focus:text-foreground data-[state=checked]:font-medium';

/**
 * Penyaring daftar publik.
 *
 * Memakai `Select` dari sistem desain, bukan `select` bawaan peramban: yang
 * bawaan digambar sistem operasi, jadi radius, bayangan, dan hurufnya tidak
 * pernah ikut tema dan jadi satu-satunya elemen yang terlihat asing di halaman.
 *
 * Namanya ikut tercetak di pemicu, bukan berdiri sebagai label terpisah di
 * atasnya. Pada bilah alat yang isinya beberapa penyaring berjajar, label
 * bertumpuk membuat barisnya dua kali lebih tinggi tanpa menambah kejelasan —
 * sementara "Kategori │ Semua artikel" sudah menjelaskan dirinya sendiri.
 */
export default function FilterSelect({
    id,
    label,
    value,
    options,
    resetLabel,
    onChange,
}: Props) {
    const isFiltered = resetLabel !== undefined && value !== '';

    return (
        <Select
            value={value === '' ? NO_FILTER : value}
            onValueChange={(next) =>
                onChange(next === NO_FILTER ? '' : next)
            }
        >
            <SelectTrigger
                id={id}
                aria-label={label}
                className={cn(
                    'h-11 w-full rounded-full border-border/70 bg-card px-4 shadow-card transition-colors sm:w-auto',
                    'hover:border-foreground/20',
                    isFiltered && 'border-foreground/25',
                )}
            >
                <span className="flex min-w-0 items-center gap-2.5">
                    <span className="shrink-0 text-muted-foreground">
                        {label}
                    </span>
                    <span aria-hidden className="h-4 w-px shrink-0 bg-border" />
                    <SelectValue className="truncate font-medium" />
                </span>
            </SelectTrigger>

            <SelectContent
                align="start"
                className="rounded-2xl border-border/70 p-1.5 shadow-dropdown"
            >
                {resetLabel !== undefined && (
                    <SelectItem value={NO_FILTER} className={itemClass}>
                        {resetLabel}
                    </SelectItem>
                )}

                {options.map((option) => (
                    <SelectItem
                        key={option.value}
                        value={option.value}
                        className={itemClass}
                    >
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
