import ContentForm from '@/components/shared/content-form';
import type { ContentField } from '@/components/shared/content-form';
import {
    index as itemsIndex,
    store as storeItem,
    update as updateItem,
} from '@/routes/admin/team-members';
import type { TeamMember } from '@/types/models';

const LOCALE = 'id';

type Props = {
    item: TeamMember | null;
};

export default function TeamMemberForm({ item }: Props) {
    const fields: ContentField[] = [
        {
            type: 'text',
            name: 'name',
            label: 'Nama lengkap',
            placeholder: 'Contoh: Adi Wicaksono',
            required: true,
        },
        {
            type: 'translated',
            name: 'position',
            label: 'Jabatan',
            placeholder: 'Contoh: Operations Manager',
            required: true,
        },
        {
            type: 'translated-textarea',
            name: 'bio',
            label: 'Bio singkat',
            placeholder: 'Pengalaman yang relevan dengan ekspor briket.',
        },
        {
            type: 'text',
            name: 'linkedin_url',
            label: 'Alamat LinkedIn',
            placeholder: 'https://www.linkedin.com/in/...',
            dir: 'ltr',
        },
        {
            type: 'image',
            name: 'photo_path',
            label: 'Foto',
            kind: 'avatar',
            currentUrl: item?.photo_url ?? null,
            rounded: true,
            hint: 'Rasio 1:1, minimal 400 × 400 piksel.',
        },
    ];

    return (
        <ContentForm
            fields={fields}
            initialValues={{
                name: item?.name ?? '',
                position: { [LOCALE]: item?.position?.[LOCALE] ?? '' },
                bio: { [LOCALE]: item?.bio?.[LOCALE] ?? '' },
                linkedin_url: item?.linkedin_url ?? '',
                photo_path_token: null,
                remove_photo_path: false,
            }}
            isActive={item?.is_active ?? true}
            isEdit={item !== null}
            submitHref={item === null ? storeItem() : updateItem(item.id)}
            cancelHref={itemsIndex()}
            saveLabel="Simpan Anggota"
            activeDescription="Anggota nonaktif tidak tampil di halaman About Us."
            panelTitle="Identitas Anggota"
            panelDescription="Nama, jabatan, dan foto yang tampil di halaman publik."
        />
    );
}
