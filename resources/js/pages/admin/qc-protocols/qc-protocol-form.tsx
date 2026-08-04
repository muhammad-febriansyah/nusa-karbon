import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/qc-protocols';
import type { QcProtocol } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: QcProtocol | null;
};

export default function QcProtocolForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'translated',
            name: 'title',
            label: 'Nama protokol',
            placeholder: 'Contoh: Drop Test',
            required: true,
        },
        {
            type: 'translated-textarea',
            name: 'description',
            label: 'Penjelasan',
            placeholder: 'Bagaimana pengujian dijalankan.',
        },
        {
            type: 'translated',
            name: 'result_label',
            label: 'Label hasil',
            placeholder: 'Contoh: Breakage tolerance',
        },
        {
            type: 'text',
            name: 'result_value',
            label: 'Nilai hasil',
            placeholder: 'Contoh: < 2.5%',
            dir: 'ltr',
        },
        {
            type: 'image',
            name: 'image_path',
            label: 'Gambar pengujian',
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
                result_label: { [LOCALE]: item?.result_label?.[LOCALE] ?? '' },
                result_value: item?.result_value ?? '',
                image_path_token: null,
                remove_image_path: false,
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Protokol"
            activeDescription="Protokol nonaktif tidak tampil di halaman publik."
            panelTitle="Isi Protokol"
            panelDescription="Nama pengujian, penjelasan, dan angka hasil yang dijamin."
        />
    );
}
