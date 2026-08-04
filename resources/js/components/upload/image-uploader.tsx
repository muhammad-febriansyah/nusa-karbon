import { ImageIcon } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useFileUpload } from '@/hooks/use-file-upload';
import type { UploadKind } from '@/lib/upload-rules';
import Dropzone from './dropzone';
import UploadProgress from './upload-progress';

type Props = {
    kind?: UploadKind;
    /** Token berkas sementara; dikirim bersama form saat disimpan. */
    onChange: (token: string | null) => void;
    /** URL gambar yang sudah tersimpan di server. */
    currentUrl?: string | null;
    disabled?: boolean;
    hint?: string;
};

/**
 * Unggah satu gambar dengan pratinjau instan, progress, dan pembatalan.
 * Untuk field berasio wajib 1:1 pakai `AvatarUploader` yang sudah memuat crop.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
export default function ImageUploader({
    kind = 'gallery',
    onChange,
    currentUrl,
    disabled,
    hint,
}: Props) {
    const { items, add, cancel, remove, retry } = useFileUpload(kind);

    const item = items.at(-1) ?? null;

    // Nilai terakhir yang dilaporkan ke parent; `undefined` berarti belum
    // pernah melapor.
    const reported = useRef<string | null | undefined>(undefined);

    useEffect(() => {
        const token = item?.status === 'done' ? item.token : null;

        // Render pertama tanpa unggahan bukan berarti "kosongkan". Melaporkan
        // `null` di sini menimpa berkas yang sudah tersimpan dengan nilai kosong
        // begitu form disimpan lagi.
        if (reported.current === undefined && token === null) {
            reported.current = null;

            return;
        }

        if (reported.current !== token) {
            reported.current = token;
            onChange(token);
        }
        // `onChange` sengaja tidak diikutkan agar tidak memicu ulang saat
        // parent membuat fungsi baru tiap render.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [item?.status, item?.token]);

    const preview = item?.preview ?? currentUrl ?? null;

    return (
        <div className="flex flex-col gap-3">
            {preview && (
                <img
                    src={preview}
                    alt="Pratinjau gambar"
                    className="max-h-56 w-fit rounded-[var(--radius)] border border-border object-contain"
                />
            )}

            {!item && (
                <Dropzone
                    kind={kind}
                    disabled={disabled}
                    onFiles={(files) => add(files)}
                    hint={hint}
                />
            )}

            {item && (
                <UploadProgress
                    item={item}
                    onCancel={cancel}
                    onRemove={remove}
                    onRetry={retry}
                    thumbnail={
                        item.preview ? (
                            <img
                                src={item.preview}
                                alt=""
                                className="size-10 shrink-0 rounded-[var(--radius)] object-cover"
                            />
                        ) : (
                            <ImageIcon
                                aria-hidden
                                className="size-10 shrink-0 rounded-[var(--radius)] bg-muted p-2.5 text-muted-foreground"
                            />
                        )
                    }
                />
            )}
        </div>
    );
}
