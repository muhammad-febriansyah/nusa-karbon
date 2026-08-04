/**
 * Cerminan `App\Enums\UploadKind` di sisi klien. Validasi dijalankan sebelum
 * request dikirim supaya pengguna tidak menunggu unggahan yang pasti ditolak.
 *
 * Bila aturan di enum PHP berubah, berkas ini harus ikut diperbarui.
 *
 * @see app/Enums/UploadKind.php
 * @see docs/07-admin-ui-standards.md bagian 6.2 & 6.3
 */
export type UploadKind =
    'product_cover' | 'gallery' | 'logo' | 'avatar' | 'document' | 'video';

type Rule = {
    label: string;
    extensions: string[];
    mimeTypes: string[];
    maxBytes: number;
    minWidth?: number;
    minHeight?: number;
};

const MB = 1024 * 1024;

export const uploadRules: Record<UploadKind, Rule> = {
    product_cover: {
        label: 'Sampul produk',
        extensions: ['jpg', 'jpeg', 'png', 'webp'],
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        maxBytes: 5 * MB,
        minWidth: 1200,
        minHeight: 900,
    },
    gallery: {
        label: 'Galeri',
        extensions: ['jpg', 'jpeg', 'png', 'webp'],
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        maxBytes: 5 * MB,
        minWidth: 800,
        minHeight: 600,
    },
    logo: {
        label: 'Logo & ikon',
        extensions: ['png', 'webp', 'svg'],
        mimeTypes: ['image/png', 'image/webp', 'image/svg+xml'],
        maxBytes: 1 * MB,
        minWidth: 200,
        minHeight: 200,
    },
    avatar: {
        label: 'Foto profil',
        extensions: ['jpg', 'jpeg', 'png', 'webp'],
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
        maxBytes: 2 * MB,
        minWidth: 400,
        minHeight: 400,
    },
    document: {
        label: 'Dokumen',
        extensions: ['pdf', 'xlsx'],
        mimeTypes: [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        maxBytes: 10 * MB,
    },
    video: {
        label: 'Video',
        extensions: ['mp4', 'webm'],
        mimeTypes: ['video/mp4', 'video/webm'],
        maxBytes: 50 * MB,
    },
};

export const formatBytes = (bytes: number): string => {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < MB) {
        return `${(bytes / 1024).toFixed(0)} KB`;
    }

    return `${(bytes / MB).toFixed(1).replace('.', ',')} MB`;
};

const extensionOf = (name: string) =>
    name.includes('.') ? name.split('.').pop()!.toLowerCase() : '';

/**
 * Memeriksa tipe dan ukuran. Mengembalikan pesan error, atau null bila lolos.
 * Pesan menyebut penyebab dan cara memperbaikinya.
 */
export function validateFile(file: File, kind: UploadKind): string | null {
    const rule = uploadRules[kind];
    const extension = extensionOf(file.name);

    const typeOk =
        rule.mimeTypes.includes(file.type) ||
        rule.extensions.includes(extension);

    if (!typeOk) {
        return `Format .${extension || 'ini'} belum didukung. Gunakan ${rule.extensions
            .map((item) => item.toUpperCase())
            .join(', ')}.`;
    }

    if (file.size > rule.maxBytes) {
        return `Ukuran file ${formatBytes(file.size)}. Maksimal ${formatBytes(
            rule.maxBytes,
        )}. Kompres file terlebih dahulu.`;
    }

    return null;
}

/**
 * Memeriksa dimensi minimal. SVG dilewati karena tidak punya dimensi raster.
 */
export function validateDimensions(
    file: File,
    kind: UploadKind,
): Promise<string | null> {
    const rule = uploadRules[kind];

    if (
        rule.minWidth === undefined ||
        rule.minHeight === undefined ||
        file.type === 'image/svg+xml'
    ) {
        return Promise.resolve(null);
    }

    return new Promise((resolve) => {
        const url = URL.createObjectURL(file);
        const image = new Image();

        image.onload = () => {
            URL.revokeObjectURL(url);

            if (
                image.naturalWidth < rule.minWidth! ||
                image.naturalHeight < rule.minHeight!
            ) {
                resolve(
                    `Resolusi gambar ${image.naturalWidth} × ${image.naturalHeight}. Minimal ${rule.minWidth} × ${rule.minHeight} agar tidak pecah saat ditampilkan.`,
                );

                return;
            }

            resolve(null);
        };

        image.onerror = () => {
            URL.revokeObjectURL(url);
            resolve('Gambar tidak dapat dibaca. Coba file lain.');
        };

        image.src = url;
    });
}
