import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/shipping-partners';
import type { ShippingPartner } from '@/types/models';

type Props = {
    item: ShippingPartner | null;
};

export default function ShippingPartnerForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'text',
            name: 'name',
            label: 'Nama mitra',
            placeholder: 'Contoh: Maersk',
            required: true,
        },
        {
            type: 'text',
            name: 'website_url',
            label: 'Alamat situs',
            placeholder: 'https://www.maersk.com',
            dir: 'ltr',
        },
        {
            type: 'image',
            name: 'logo_path',
            label: 'Logo mitra',
            kind: 'logo',
            currentUrl: item?.logo_url ?? null,
            hint: 'PNG atau SVG berlatar transparan.',
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                name: item?.name ?? '',
                website_url: item?.website_url ?? '',
                logo_path_token: null,
                remove_logo_path: false,
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Mitra"
            activeDescription="Mitra nonaktif tidak tampil di halaman publik."
            panelTitle="Identitas Mitra"
            panelDescription="Nama dan logo seperti yang dikenal di dokumen pengiriman."
        />
    );
}
