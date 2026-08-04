import { FileSpreadsheet, FileText } from 'lucide-react';
import { useEffect } from 'react';
import { useFileUpload } from '@/hooks/use-file-upload';
import { cn } from '@/lib/utils';
import Dropzone from './dropzone';
import UploadProgress from './upload-progress';

type Props = {
    onChange: (tokens: string[]) => void;
    multiple?: boolean;
    max?: number;
    disabled?: boolean;
};

const isSpreadsheet = (name: string) => name.toLowerCase().endsWith('.xlsx');

/**
 * Unggah dokumen PDF dan XLSX. Ikon menyesuaikan tipe berkas, nama dan ukuran
 * ditampilkan agar admin bisa memastikan berkas yang benar.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
export default function DocumentUploader({
    onChange,
    multiple = false,
    max = 10,
    disabled,
}: Props) {
    const { items, add, cancel, remove, retry, tokens } =
        useFileUpload('document');

    useEffect(() => {
        onChange(tokens);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [tokens.join(',')]);

    const reachedMax = items.length >= (multiple ? max : 1);

    return (
        <div className="flex flex-col gap-3">
            {!reachedMax && (
                <Dropzone
                    kind="document"
                    multiple={multiple}
                    disabled={disabled}
                    acceptPaste={false}
                    onFiles={(files) =>
                        add(files.slice(0, (multiple ? max : 1) - items.length))
                    }
                />
            )}

            {items.map((item) => {
                const spreadsheet = isSpreadsheet(item.name);
                const Icon = spreadsheet ? FileSpreadsheet : FileText;

                return (
                    <UploadProgress
                        key={item.id}
                        item={item}
                        onCancel={cancel}
                        onRemove={remove}
                        onRetry={retry}
                        thumbnail={
                            <Icon
                                aria-hidden
                                className={cn(
                                    'size-10 shrink-0 rounded-[var(--radius)] p-2.5',
                                    spreadsheet
                                        ? 'bg-success/12 text-success'
                                        : 'bg-destructive/12 text-destructive',
                                )}
                            />
                        }
                    />
                );
            })}
        </div>
    );
}
