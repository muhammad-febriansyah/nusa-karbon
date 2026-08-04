import { Camera, Trash2, X, ZoomIn } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

/** Sisi keluaran dalam piksel. Sesuai dimensi minimal 400x400 di docs. */
const OUTPUT_SIZE = 512;
const MAX_BYTES = 2 * 1024 * 1024;
const ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

type Props = {
    /** URL foto yang tersimpan di server, null bila belum ada. */
    currentUrl?: string | null;
    /** Nama pengguna, dipakai untuk inisial saat belum ada foto. */
    fallback: string;
    /** Dipanggil dengan hasil potongan 1:1, atau null saat dibatalkan. */
    onChange: (file: File | null) => void;
    onRemove?: () => void;
    disabled?: boolean;
};

type Draft = {
    src: string;
    image: HTMLImageElement;
};

export default function AvatarUploader({
    currentUrl,
    fallback,
    onChange,
    onRemove,
    disabled,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const dragState = useRef<{ x: number; y: number } | null>(null);

    const [draft, setDraft] = useState<Draft | null>(null);
    const [preview, setPreview] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [zoom, setZoom] = useState(1);
    const [offset, setOffset] = useState({ x: 0, y: 0 });

    const shown = preview ?? currentUrl ?? null;

    const initials = fallback
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');

    const pickFile = (file: File) => {
        setError(null);

        if (!ACCEPTED.includes(file.type)) {
            setError(
                `Format ${file.type || 'ini'} belum didukung. Gunakan JPG, PNG, atau WebP.`,
            );

            return;
        }

        if (file.size > MAX_BYTES) {
            const size = (file.size / 1024 / 1024).toFixed(1).replace('.', ',');
            setError(
                `Ukuran file ${size} MB. Maksimal 2 MB. Kompres gambar terlebih dahulu.`,
            );

            return;
        }

        const src = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            if (image.naturalWidth < 400 || image.naturalHeight < 400) {
                setError(
                    `Resolusi gambar ${image.naturalWidth} × ${image.naturalHeight}. Minimal 400 × 400 agar tidak pecah saat ditampilkan.`,
                );
                URL.revokeObjectURL(src);

                return;
            }

            setZoom(1);
            setOffset({ x: 0, y: 0 });
            setDraft({ src, image });
        };

        image.onerror = () => {
            setError('Gambar tidak dapat dibaca. Coba file lain.');
            URL.revokeObjectURL(src);
        };

        image.src = src;
    };

    const closeCropper = useCallback(() => {
        setDraft((current) => {
            if (current) {
                URL.revokeObjectURL(current.src);
            }

            return null;
        });
    }, []);

    // Menggambar ulang tiap kali zoom atau posisi berubah.
    useEffect(() => {
        const canvas = canvasRef.current;

        if (!draft || !canvas) {
            return;
        }

        const context = canvas.getContext('2d');

        if (!context) {
            return;
        }

        const { image } = draft;
        // Skala minimum agar sisi terpendek menutupi seluruh kanvas.
        const base =
            OUTPUT_SIZE / Math.min(image.naturalWidth, image.naturalHeight);
        const scale = base * zoom;
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;

        context.clearRect(0, 0, OUTPUT_SIZE, OUTPUT_SIZE);
        context.fillStyle = '#ffffff';
        context.fillRect(0, 0, OUTPUT_SIZE, OUTPUT_SIZE);
        context.drawImage(
            image,
            (OUTPUT_SIZE - width) / 2 + offset.x,
            (OUTPUT_SIZE - height) / 2 + offset.y,
            width,
            height,
        );
    }, [draft, zoom, offset]);

    const applyCrop = () => {
        const canvas = canvasRef.current;

        if (!canvas) {
            return;
        }

        canvas.toBlob(
            (blob) => {
                if (!blob) {
                    setError('Gagal memotong gambar. Coba lagi.');

                    return;
                }

                const file = new File([blob], 'avatar.webp', {
                    type: 'image/webp',
                });

                setPreview((current) => {
                    if (current) {
                        URL.revokeObjectURL(current);
                    }

                    return URL.createObjectURL(file);
                });

                onChange(file);
                closeCropper();
            },
            'image/webp',
            0.9,
        );
    };

    const startDrag = (event: React.PointerEvent<HTMLCanvasElement>) => {
        event.currentTarget.setPointerCapture(event.pointerId);
        dragState.current = {
            x: event.clientX - offset.x,
            y: event.clientY - offset.y,
        };
    };

    const moveDrag = (event: React.PointerEvent<HTMLCanvasElement>) => {
        if (!dragState.current) {
            return;
        }

        setOffset({
            x: event.clientX - dragState.current.x,
            y: event.clientY - dragState.current.y,
        });
    };

    const endDrag = () => {
        dragState.current = null;
    };

    return (
        <div className="flex flex-col gap-3">
            <div className="flex items-center gap-4">
                <span className="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted text-xl font-medium text-muted-foreground">
                    {shown ? (
                        <img
                            src={shown}
                            alt="Foto profil"
                            className="size-full object-cover"
                        />
                    ) : (
                        initials
                    )}
                </span>

                <div className="flex flex-wrap gap-2">
                    <Button
                        type="button"
                        variant="upload"
                        size="sm"
                        disabled={disabled}
                        onClick={() => inputRef.current?.click()}
                    >
                        <Camera />
                        {shown ? 'Ganti Foto' : 'Unggah Foto'}
                    </Button>

                    {shown && onRemove && (
                        <Button
                            type="button"
                            variant="delete"
                            size="sm"
                            disabled={disabled}
                            onClick={() => {
                                setPreview((current) => {
                                    if (current) {
                                        URL.revokeObjectURL(current);
                                    }

                                    return null;
                                });
                                onChange(null);
                                onRemove();
                            }}
                        >
                            <Trash2 />
                            Hapus Foto
                        </Button>
                    )}
                </div>
            </div>

            <p className="text-xs text-muted-foreground">
                JPG, PNG, atau WebP. Maksimal 2 MB, minimal 400 × 400 piksel.
                Gambar dipotong menjadi persegi.
            </p>

            {error && <p className="text-xs text-destructive">{error}</p>}

            <input
                ref={inputRef}
                type="file"
                accept={ACCEPTED.join(',')}
                className="hidden"
                onChange={(event) => {
                    const file = event.target.files?.[0];

                    if (file) {
                        pickFile(file);
                    }

                    event.target.value = '';
                }}
            />

            <Dialog
                open={draft !== null}
                onOpenChange={(open) => !open && closeCropper()}
            >
                <DialogContent>
                    <DialogTitle>Sesuaikan foto profil</DialogTitle>
                    <DialogDescription>
                        Geser gambar untuk mengatur posisi, lalu atur perbesaran
                        sampai pas.
                    </DialogDescription>

                    <div className="flex flex-col gap-4">
                        <canvas
                            ref={canvasRef}
                            width={OUTPUT_SIZE}
                            height={OUTPUT_SIZE}
                            onPointerDown={startDrag}
                            onPointerMove={moveDrag}
                            onPointerUp={endDrag}
                            onPointerCancel={endDrag}
                            className={cn(
                                'mx-auto aspect-square w-full max-w-64 touch-none rounded-full border border-border',
                                'cursor-grab active:cursor-grabbing',
                            )}
                        />

                        <label className="flex items-center gap-3">
                            <ZoomIn
                                aria-hidden
                                className="size-4 shrink-0 text-muted-foreground"
                            />
                            <span className="sr-only">Perbesaran</span>
                            <input
                                type="range"
                                min={1}
                                max={3}
                                step={0.01}
                                value={zoom}
                                onChange={(event) =>
                                    setZoom(Number(event.target.value))
                                }
                                className="w-full accent-primary"
                            />
                        </label>
                    </div>

                    <DialogFooter className="gap-2">
                        <Button
                            type="button"
                            variant="cancel"
                            onClick={closeCropper}
                        >
                            <X />
                            Batal
                        </Button>
                        <Button
                            type="button"
                            variant="save"
                            onClick={applyCrop}
                        >
                            <Camera />
                            Pakai Foto Ini
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    );
}
