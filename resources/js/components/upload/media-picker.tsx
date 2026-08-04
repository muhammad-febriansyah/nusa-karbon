import { ImageIcon, Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { picker as mediaPicker } from '@/routes/admin/media';

type PickerFile = {
    path: string;
    name: string;
    url: string;
    dimensions: { width: number; height: number } | null;
};

/**
 * Dialog pemilih gambar dari Media Library, dipakai tombol sisip gambar di
 * editor TipTap. Mengembalikan URL berkas terpilih, atau null bila dibatalkan.
 *
 * @see docs/09-tasks.md bagian 4.4
 */
export function useMediaPicker() {
    const [open, setOpen] = useState(false);
    const resolverRef = useRef<((url: string | null) => void) | null>(null);

    const pickImage = () =>
        new Promise<string | null>((resolve) => {
            resolverRef.current = resolve;
            setOpen(true);
        });

    const finish = (url: string | null) => {
        resolverRef.current?.(url);
        resolverRef.current = null;
        setOpen(false);
    };

    const dialog = (
        <MediaPickerDialog
            open={open}
            // Menutup dialog tanpa memilih tetap harus menyelesaikan promise,
            // kalau tidak editor menunggu selamanya.
            onOpenChange={(next) => !next && finish(null)}
            onSelect={(url) => finish(url)}
        />
    );

    return { pickImage, dialog };
}

function MediaPickerDialog({
    open,
    onOpenChange,
    onSelect,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onSelect: (url: string) => void;
}) {
    const [search, setSearch] = useState('');
    const [files, setFiles] = useState<PickerFile[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!open) {
            return;
        }

        const controller = new AbortController();
        const timer = window.setTimeout(() => {
            setLoading(true);

            fetch(mediaPicker({ query: { search } }).url, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            })
                .then((response) => response.json())
                .then((data: { files: PickerFile[] }) => setFiles(data.files))
                .catch(() => undefined)
                .finally(() => setLoading(false));
        }, 200);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [open, search]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-3xl">
                <DialogTitle>Pilih Gambar</DialogTitle>
                <DialogDescription>
                    Gambar yang sudah pernah diunggah ke Media Library.
                </DialogDescription>

                <div className="relative">
                    <Search
                        aria-hidden
                        className="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Cari nama berkas…"
                        className="ps-9"
                    />
                </div>

                {loading ? (
                    <div className="flex h-40 items-center justify-center">
                        <Spinner />
                    </div>
                ) : files.length === 0 ? (
                    <div className="flex h-40 flex-col items-center justify-center gap-2 text-sm text-muted-foreground">
                        <ImageIcon aria-hidden className="size-6" />
                        Belum ada gambar yang cocok.
                    </div>
                ) : (
                    <ul className="grid max-h-80 grid-cols-2 gap-3 overflow-y-auto sm:grid-cols-4">
                        {files.map((file) => (
                            <li key={file.path}>
                                <button
                                    type="button"
                                    onClick={() => onSelect(file.url)}
                                    className="flex w-full flex-col overflow-hidden rounded-[var(--radius)] border border-border transition-colors hover:border-primary"
                                >
                                    <span className="flex h-24 items-center justify-center bg-muted">
                                        <img
                                            src={file.url}
                                            alt={file.name}
                                            className="size-full object-contain p-1.5"
                                        />
                                    </span>
                                    <span
                                        dir="ltr"
                                        className="line-clamp-2 px-2 py-1.5 text-start text-[11px] break-all"
                                    >
                                        {file.name}
                                    </span>
                                </button>
                            </li>
                        ))}
                    </ul>
                )}
            </DialogContent>
        </Dialog>
    );
}
