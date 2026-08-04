import { router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Spinner } from '@/components/ui/spinner';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import { search as searchRoute } from '@/routes/admin';

type Hit = {
    id: number;
    title: string;
    subtitle: string | null;
    url: string;
};

type Group = {
    group: string;
    items: Hit[];
};

/** Sama dengan `AdminSearchService::MIN_LENGTH`. */
const MIN_LENGTH = 2;

/**
 * Pencarian lintas modul di header admin.
 *
 * Permintaan ditahan 300 ms setelah ketikan terakhir, dan permintaan lama
 * dibatalkan begitu ada ketikan baru — tanpa itu balasan yang datang terlambat
 * bisa menimpa hasil untuk kata kunci yang lebih baru.
 */
export default function GlobalSearch() {
    const [open, setOpen] = useState(false);
    const [term, setTerm] = useState('');
    // Hasil disimpan bersama kata kuncinya, bukan sebagai daftar lepas: dari
    // pasangan itu status "sedang memuat" bisa diturunkan, tanpa state kedua
    // yang bisa tertinggal saat permintaan dibatalkan.
    const [result, setResult] = useState<{ query: string; groups: Group[] }>({
        query: '',
        groups: [],
    });
    const debounced = useDebouncedValue(term, 300);
    const query = debounced.trim();
    const groups = result.query === query ? result.groups : [];
    const loading = query.length >= MIN_LENGTH && result.query !== query;

    useEffect(() => {
        const onKey = (event: KeyboardEvent) => {
            if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
                event.preventDefault();
                setOpen((current) => !current);
            }
        };

        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, []);

    useEffect(() => {
        if (query.length < MIN_LENGTH) {
            return;
        }

        const controller = new AbortController();

        fetch(`${searchRoute().url}?q=${encodeURIComponent(query)}`, {
            headers: { Accept: 'application/json' },
            signal: controller.signal,
        })
            .then((response) =>
                response.ok ? response.json() : { groups: [] },
            )
            .then((data: { groups?: Group[] }) =>
                setResult({ query, groups: data.groups ?? [] }),
            )
            .catch(() => {
                // Permintaan yang dibatalkan memang tidak dipakai lagi; sisanya
                // ditandai selesai supaya spinner tidak berputar selamanya.
                if (!controller.signal.aborted) {
                    setResult({ query, groups: [] });
                }
            });

        return () => controller.abort();
    }, [query]);

    const go = (url: string) => {
        setOpen(false);
        setTerm('');
        router.visit(url);
    };

    const tooShort = term.trim().length > 0 && term.trim().length < MIN_LENGTH;

    return (
        <>
            <button
                type="button"
                onClick={() => setOpen(true)}
                className="hidden h-8.5 w-70 items-center gap-2 rounded-md border border-input bg-background ps-3 pe-2 text-[13px] text-muted-foreground transition-colors hover:border-ring md:flex"
            >
                <Search aria-hidden className="size-[15px] shrink-0" />
                <span className="flex-1 text-start">
                    Cari produk, RFQ, artikel…
                </span>
                <kbd
                    dir="ltr"
                    className="rounded border border-border px-1.5 py-0.5 text-[10px] font-medium"
                >
                    ⌘K
                </kbd>
            </button>

            <CommandDialog
                open={open}
                onOpenChange={setOpen}
                title="Pencarian"
                description="Cari produk, RFQ, artikel, FAQ, sertifikat, dan perwakilan."
                // Penyaringan dilakukan server; cmdk tidak boleh menyaring ulang
                // hasil yang sudah cocok.
                shouldFilter={false}
            >
                <CommandInput
                    value={term}
                    onValueChange={setTerm}
                    placeholder="Ketik nama produk, nomor RFQ, atau judul artikel…"
                />
                <CommandList>
                    {loading && (
                        <div className="flex items-center justify-center gap-2 py-6 text-sm text-muted-foreground">
                            <Spinner />
                            Mencari…
                        </div>
                    )}

                    {!loading && tooShort && (
                        <div className="py-6 text-center text-sm text-muted-foreground">
                            Ketik minimal {MIN_LENGTH} huruf.
                        </div>
                    )}

                    {!loading && !tooShort && groups.length === 0 && (
                        <CommandEmpty>
                            {term.trim() === ''
                                ? 'Mulai mengetik untuk mencari.'
                                : 'Tidak ada hasil.'}
                        </CommandEmpty>
                    )}

                    {!loading &&
                        groups.map((group) => (
                            <CommandGroup
                                key={group.group}
                                heading={group.group}
                            >
                                {group.items.map((hit) => (
                                    <CommandItem
                                        key={`${group.group}-${hit.id}`}
                                        value={`${group.group}-${hit.id}`}
                                        onSelect={() => go(hit.url)}
                                    >
                                        <span className="flex min-w-0 flex-col">
                                            <span className="truncate">
                                                {hit.title}
                                            </span>
                                            {hit.subtitle && (
                                                <span className="truncate text-xs text-muted-foreground">
                                                    {hit.subtitle}
                                                </span>
                                            )}
                                        </span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        ))}
                </CommandList>
            </CommandDialog>
        </>
    );
}
