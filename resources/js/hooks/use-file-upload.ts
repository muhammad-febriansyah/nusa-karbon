import { useCallback, useRef, useState } from 'react';
import type { UploadKind } from '@/lib/upload-rules';
import { validateDimensions, validateFile } from '@/lib/upload-rules';
import { store as tempUploadStore } from '@/routes/admin/uploads/temp';
import { destroy as tempUploadDestroy } from '@/routes/admin/uploads/temp';

export type UploadItem = {
    /** Id lokal, dipakai sebagai key React sebelum token server tersedia. */
    id: string;
    file: File;
    name: string;
    size: number;
    /** Object URL untuk pratinjau instan, null untuk non-gambar. */
    preview: string | null;
    progress: number;
    status: 'validating' | 'uploading' | 'done' | 'error' | 'cancelled';
    /** Token dari server setelah unggahan selesai. */
    token: string | null;
    error: string | null;
};

const csrfToken = () =>
    document
        .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
        ?.getAttribute('content') ?? '';

const readCookie = (name: string) =>
    document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`))
        ?.split('=')[1] ?? '';

/**
 * Mengunggah berkas satu per satu ke endpoint sementara memakai XHR, sehingga
 * progress bisa dilaporkan dan unggahan yang berjalan bisa dibatalkan.
 * Kegagalan satu berkas tidak membatalkan berkas lain.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.1
 */
export function useFileUpload(kind: UploadKind) {
    const [items, setItems] = useState<UploadItem[]>([]);
    const controllers = useRef(new Map<string, XMLHttpRequest>());

    const patch = useCallback((id: string, changes: Partial<UploadItem>) => {
        setItems((current) =>
            current.map((item) =>
                item.id === id ? { ...item, ...changes } : item,
            ),
        );
    }, []);

    const upload = useCallback(
        (id: string, file: File) => {
            const request = new XMLHttpRequest();
            controllers.current.set(id, request);

            const body = new FormData();
            body.append('file', file);
            body.append('kind', kind);

            request.open('POST', tempUploadStore().url);
            request.setRequestHeader('Accept', 'application/json');
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            const csrf = csrfToken();

            if (csrf !== '') {
                request.setRequestHeader('X-CSRF-TOKEN', csrf);
            } else {
                request.setRequestHeader(
                    'X-XSRF-TOKEN',
                    decodeURIComponent(readCookie('XSRF-TOKEN')),
                );
            }

            request.upload.onprogress = (event) => {
                if (!event.lengthComputable) {
                    return;
                }

                patch(id, {
                    progress: Math.round((event.loaded / event.total) * 100),
                });
            };

            request.onload = () => {
                controllers.current.delete(id);

                if (request.status === 201) {
                    const payload = JSON.parse(request.responseText) as {
                        token: string;
                    };

                    patch(id, {
                        status: 'done',
                        progress: 100,
                        token: payload.token,
                    });

                    return;
                }

                let message = 'Berkas ditolak server. Coba berkas lain.';

                try {
                    const payload = JSON.parse(request.responseText) as {
                        message?: string;
                        errors?: Record<string, string[]>;
                    };

                    message =
                        payload.errors?.file?.[0] ?? payload.message ?? message;
                } catch {
                    // Respons bukan JSON; pakai pesan bawaan.
                }

                patch(id, { status: 'error', error: message });
            };

            request.onerror = () => {
                controllers.current.delete(id);
                patch(id, {
                    status: 'error',
                    error: 'Unggahan terputus. Periksa koneksi lalu coba lagi.',
                });
            };

            request.onabort = () => {
                controllers.current.delete(id);
                patch(id, { status: 'cancelled', error: null });
            };

            request.send(body);
        },
        [kind, patch],
    );

    const add = useCallback(
        async (files: File[]) => {
            for (const file of files) {
                const id = `${file.name}-${file.size}-${Math.random()
                    .toString(36)
                    .slice(2, 8)}`;

                const isImage = file.type.startsWith('image/');

                setItems((current) => [
                    ...current,
                    {
                        id,
                        file,
                        name: file.name,
                        size: file.size,
                        preview: isImage ? URL.createObjectURL(file) : null,
                        progress: 0,
                        status: 'validating',
                        token: null,
                        error: null,
                    },
                ]);

                const typeError = validateFile(file, kind);

                if (typeError !== null) {
                    patch(id, { status: 'error', error: typeError });

                    continue;
                }

                const dimensionError = await validateDimensions(file, kind);

                if (dimensionError !== null) {
                    patch(id, { status: 'error', error: dimensionError });

                    continue;
                }

                patch(id, { status: 'uploading' });
                upload(id, file);
            }
        },
        [kind, patch, upload],
    );

    const cancel = useCallback((id: string) => {
        controllers.current.get(id)?.abort();
    }, []);

    const remove = useCallback((id: string) => {
        controllers.current.get(id)?.abort();

        setItems((current) => {
            const target = current.find((item) => item.id === id);

            if (target?.preview) {
                URL.revokeObjectURL(target.preview);
            }

            if (target?.token) {
                // Membersihkan berkas sementara di server; kegagalan diabaikan
                // karena berkas yatim tetap terhapus oleh pembersihan berkala.
                void fetch(tempUploadDestroy(target.token).url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-XSRF-TOKEN': decodeURIComponent(
                            readCookie('XSRF-TOKEN'),
                        ),
                    },
                }).catch(() => undefined);
            }

            return current.filter((item) => item.id !== id);
        });
    }, []);

    const retry = useCallback(
        (id: string) => {
            const target = items.find((item) => item.id === id);

            if (!target) {
                return;
            }

            patch(id, { status: 'uploading', progress: 0, error: null });
            upload(id, target.file);
        },
        [items, patch, upload],
    );

    const reorder = useCallback((next: UploadItem[]) => setItems(next), []);

    const reset = useCallback(() => {
        items.forEach((item) => {
            if (item.preview) {
                URL.revokeObjectURL(item.preview);
            }
        });

        setItems([]);
    }, [items]);

    const tokens = items
        .filter((item) => item.status === 'done' && item.token !== null)
        .map((item) => item.token!);

    return { items, add, cancel, remove, retry, reorder, reset, tokens };
}
