import { AlertCircle, CheckCircle2, RotateCcw, X } from 'lucide-react';
import type { UploadItem } from '@/hooks/use-file-upload';
import { formatBytes } from '@/lib/upload-rules';
import { cn } from '@/lib/utils';

type Props = {
    item: UploadItem;
    onCancel: (id: string) => void;
    onRemove: (id: string) => void;
    onRetry: (id: string) => void;
    /** Pratinjau kotak untuk gambar; dimatikan pada dokumen. */
    thumbnail?: React.ReactNode;
};

export default function UploadProgress({
    item,
    onCancel,
    onRemove,
    onRetry,
    thumbnail,
}: Props) {
    const isBusy = item.status === 'uploading' || item.status === 'validating';

    return (
        <div className="flex items-center gap-3 rounded-[var(--radius)] border border-border p-3">
            {thumbnail}

            <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2">
                    <p className="truncate text-[13px] font-medium">
                        {item.name}
                    </p>
                    <span className="shrink-0 text-xs text-muted-foreground tabular-nums">
                        {formatBytes(item.size)}
                    </span>
                </div>

                {isBusy && (
                    <div className="mt-1.5 flex items-center gap-2">
                        <div
                            role="progressbar"
                            aria-valuenow={item.progress}
                            aria-valuemin={0}
                            aria-valuemax={100}
                            aria-label={`Mengunggah ${item.name}`}
                            className="h-1.5 flex-1 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                style={{ inlineSize: `${item.progress}%` }}
                                className="h-full rounded-full bg-primary transition-[inline-size]"
                            />
                        </div>
                        <span className="text-xs text-muted-foreground tabular-nums">
                            {item.progress}%
                        </span>
                    </div>
                )}

                {item.status === 'error' && (
                    <p className="mt-1 flex items-start gap-1.5 text-xs text-destructive">
                        <AlertCircle
                            aria-hidden
                            className="mt-0.5 size-3.5 shrink-0"
                        />
                        {item.error}
                    </p>
                )}

                {item.status === 'cancelled' && (
                    <p className="mt-1 text-xs text-muted-foreground">
                        Unggahan dibatalkan.
                    </p>
                )}
            </div>

            <div className="flex shrink-0 items-center gap-1">
                {item.status === 'done' && (
                    <CheckCircle2
                        aria-hidden
                        className={cn('size-4 text-success')}
                    />
                )}

                {(item.status === 'error' || item.status === 'cancelled') && (
                    <button
                        type="button"
                        onClick={() => onRetry(item.id)}
                        className="rounded-[var(--radius)] p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                    >
                        <RotateCcw className="size-4" />
                        <span className="sr-only">Coba unggah lagi</span>
                    </button>
                )}

                <button
                    type="button"
                    onClick={() =>
                        isBusy ? onCancel(item.id) : onRemove(item.id)
                    }
                    className="rounded-[var(--radius)] p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                >
                    <X className="size-4" />
                    <span className="sr-only">
                        {isBusy ? 'Batalkan unggahan' : 'Hapus berkas'}
                    </span>
                </button>
            </div>
        </div>
    );
}
