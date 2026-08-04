import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/features';
import type { Feature } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: Feature | null;
};

export default function FeatureForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'translated',
            name: 'title',
            label: 'Judul keunggulan',
            placeholder: 'Contoh: Kualitas Konsisten',
            required: true,
        },
        {
            type: 'translated-textarea',
            name: 'description',
            label: 'Penjelasan',
            placeholder: 'Bukti konkret, bukan klaim umum.',
        },
        {
            type: 'text',
            name: 'icon',
            label: 'Ikon lucide',
            placeholder: 'Contoh: shield-check',
            dir: 'ltr',
            hint: 'Nama ikon dari lucide.dev, huruf kecil dengan tanda hubung.',
        },
        {
            type: 'image',
            name: 'image_path',
            label: 'Gambar pendukung',
            kind: 'gallery',
            currentUrl: item?.image_url ?? null,
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                title: { [LOCALE]: item?.title?.[LOCALE] ?? '' },
                description: { [LOCALE]: item?.description?.[LOCALE] ?? '' },
                icon: item?.icon ?? '',
                image_path_token: null,
                remove_image_path: false,
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Keunggulan"
            activeDescription="Keunggulan nonaktif tidak tampil di beranda."
            panelTitle="Isi Keunggulan"
            panelDescription="Judul singkat, penjelasan, dan ikon pendamping."
        />
    );
}
