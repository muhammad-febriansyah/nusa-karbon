import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/post-categories';
import type { PostCategory } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: PostCategory | null;
};

export default function PostCategoryForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'translated',
            name: 'name',
            label: 'Nama kategori',
            placeholder: 'Contoh: Panduan Ekspor',
            required: true,
        },
        {
            type: 'translated',
            name: 'slug',
            label: 'Slug',
            placeholder: 'panduan-ekspor',
            hint: 'Kosongkan untuk mengisi otomatis dari nama.',
        },
        {
            type: 'translated-textarea',
            name: 'description',
            label: 'Deskripsi',
            placeholder: 'Ringkasan isi kategori ini.',
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                name: { [LOCALE]: item?.name?.[LOCALE] ?? '' },
                slug: { [LOCALE]: item?.slug?.[LOCALE] ?? '' },
                description: { [LOCALE]: item?.description?.[LOCALE] ?? '' },
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Kategori"
            activeDescription="Kategori nonaktif tidak muncul sebagai filter di halaman blog."
            panelTitle="Identitas Kategori"
            panelDescription="Nama dan slug yang dipakai di alamat halaman kategori."
        />
    );
}
