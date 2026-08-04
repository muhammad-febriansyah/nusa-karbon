import { ImageIcon } from 'lucide-react';
import { useEffect } from 'react';
import SortableList from '@/components/shared/sortable-list';
import { useFileUpload } from '@/hooks/use-file-upload';
import type { UploadKind } from '@/lib/upload-rules';
import Dropzone from './dropzone';
import UploadProgress from './upload-progress';

type Props = {
    kind?: UploadKind;
    /** Token berkas sementara, urutannya mengikuti urutan tampilan. */
    onChange: (tokens: string[]) => void;
    max?: number;
    disabled?: boolean;
    hint?: string;
};

/**
 * Unggah banyak gambar sekaligus. Progress dilaporkan per berkas dan kegagalan
 * satu berkas tidak membatalkan yang lain. Urutan bisa diubah drag-and-drop.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
export default function MultiImageUploader({
    kind = 'gallery',
    onChange,
    max = 20,
    disabled,
    hint,
}: Props) {
    const { items, add, cancel, remove, retry, reorder, tokens } =
        useFileUpload(kind);

    useEffect(() => {
        onChange(tokens);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [tokens.join(',')]);

    const reachedMax = items.length >= max;

    return (
        <div className="flex flex-col gap-3">
            <Dropzone
                kind={kind}
                multiple
                disabled={disabled || reachedMax}
                onFiles={(files) => add(files.slice(0, max - items.length))}
                hint={hint}
            />

            {reachedMax && (
                <p className="text-xs text-muted-foreground">
                    Batas {max} gambar tercapai. Hapus salah satu untuk menambah
                    yang baru.
                </p>
            )}

            {items.length > 0 && (
                <SortableList
                    items={items}
                    itemId={(item) => item.id}
                    onReorder={reorder}
                >
                    {(item) => (
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
                </SortableList>
            )}
        </div>
    );
}
