import { UploadCloud } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import type { UploadKind } from '@/lib/upload-rules';
import { formatBytes, uploadRules } from '@/lib/upload-rules';
import { cn } from '@/lib/utils';

type Props = {
    kind: UploadKind;
    multiple?: boolean;
    disabled?: boolean;
    onFiles: (files: File[]) => void;
    /** Menerima tempelan clipboard selama komponen terpasang. */
    acceptPaste?: boolean;
    hint?: string;
};

/**
 * Area unggah: drag & drop dengan umpan balik visual, klik untuk memilih, dan
 * tempel dari clipboard.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
export default function Dropzone({
    kind,
    multiple = false,
    disabled = false,
    onFiles,
    acceptPaste = true,
    hint,
}: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragging, setDragging] = useState(false);
    const rule = uploadRules[kind];

    const accept = rule.extensions.map((item) => `.${item}`).join(',');

    const emit = useCallback(
        (list: FileList | null) => {
            if (!list || list.length === 0) {
                return;
            }

            onFiles(multiple ? Array.from(list) : [list[0]]);
        },
        [multiple, onFiles],
    );

    useEffect(() => {
        if (!acceptPaste || disabled) {
            return;
        }

        const handlePaste = (event: ClipboardEvent) => {
            const files = event.clipboardData?.files;

            if (files && files.length > 0) {
                event.preventDefault();
                emit(files);
            }
        };

        window.addEventListener('paste', handlePaste);

        return () => window.removeEventListener('paste', handlePaste);
    }, [acceptPaste, disabled, emit]);

    return (
        <div
            role="button"
            tabIndex={disabled ? -1 : 0}
            aria-disabled={disabled}
            onClick={() => !disabled && inputRef.current?.click()}
            onKeyDown={(event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    inputRef.current?.click();
                }
            }}
            onDragOver={(event) => {
                event.preventDefault();

                if (!disabled) {
                    setDragging(true);
                }
            }}
            onDragLeave={() => setDragging(false)}
            onDrop={(event) => {
                event.preventDefault();
                setDragging(false);

                if (!disabled) {
                    emit(event.dataTransfer.files);
                }
            }}
            className={cn(
                'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[var(--radius)] border border-dashed border-input px-6 py-8 text-center transition-colors outline-none',
                'hover:border-primary/60 hover:bg-muted/50 focus-visible:ring-2 focus-visible:ring-ring',
                dragging && 'border-primary bg-primary/5',
                disabled && 'pointer-events-none opacity-50',
            )}
        >
            <UploadCloud
                aria-hidden
                strokeWidth={1.5}
                className="size-8 text-muted-foreground"
            />

            <p className="text-[13px] font-medium">
                Letakkan berkas di sini, atau klik untuk memilih
            </p>

            <p className="text-xs text-muted-foreground">
                {hint ??
                    `${rule.extensions.map((item) => item.toUpperCase()).join(', ')} · maksimal ${formatBytes(rule.maxBytes)}${
                        rule.minWidth
                            ? ` · minimal ${rule.minWidth} × ${rule.minHeight} piksel`
                            : ''
                    }`}
            </p>

            {acceptPaste && (
                <p className="text-xs text-muted-foreground">
                    Bisa juga menempel tangkapan layar dengan Ctrl+V.
                </p>
            )}

            <input
                ref={inputRef}
                type="file"
                accept={accept}
                multiple={multiple}
                className="hidden"
                onChange={(event) => {
                    emit(event.target.files);
                    event.target.value = '';
                }}
            />
        </div>
    );
}
