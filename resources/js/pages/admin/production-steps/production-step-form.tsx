import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/production-steps';
import type { ProductionStep } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: ProductionStep | null;
};

export default function ProductionStepForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'translated',
            name: 'title',
            label: 'Judul tahapan',
            placeholder: 'Contoh: Karbonisasi',
            required: true,
        },
        {
            type: 'translated-textarea',
            name: 'description',
            label: 'Penjelasan',
            placeholder: 'Apa yang terjadi pada tahapan ini.',
        },
        {
            type: 'image',
            name: 'image_path',
            label: 'Gambar tahapan',
            kind: 'gallery',
            currentUrl: item?.image_url ?? null,
            hint: 'Foto proses nyata, bukan ilustrasi stok.',
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                title: { [LOCALE]: item?.title?.[LOCALE] ?? '' },
                description: { [LOCALE]: item?.description?.[LOCALE] ?? '' },
                image_path_token: null,
                remove_image_path: false,
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Tahapan"
            activeDescription="Tahapan nonaktif tidak tampil di halaman publik."
            panelTitle="Isi Tahapan"
            panelDescription="Judul dan penjelasan singkat yang dibaca buyer."
        />
    );
}
