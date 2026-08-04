import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/faq-categories';
import type { FaqCategory } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: FaqCategory | null;
};

export default function FaqCategoryForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'translated',
            name: 'name',
            label: 'Nama kategori',
            placeholder: 'Contoh: Pengiriman',
            required: true,
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                name: { [LOCALE]: item?.name?.[LOCALE] ?? '' },
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Kategori"
            activeDescription="Kategori nonaktif beserta pertanyaannya tidak tampil di halaman FAQ."
            panelTitle="Identitas Kategori"
            panelDescription="Nama kelompok seperti yang dibaca pengunjung."
        />
    );
}
