import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as containerTypeEdit,
    index as containerTypesIndex,
} from '@/routes/admin/container-types';
import type { ContainerType } from '@/types/models';
import ContainerTypeForm from './container-type-form';

const LOCALE = 'id';

type Props = {
    containerType: ContainerType;
};

export default function ContainerTypeEdit({ containerType }: Props) {
    const name = containerType.name?.[LOCALE] ?? containerType.code;

    return (
        <>
            <PageHeader
                title={`Ubah ${name}`}
                description="Perbarui kapasitas muat dan dimensi internal kontainer ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tipe Kontainer', href: containerTypesIndex() },
                    { title: name, href: containerTypeEdit(containerType.id) },
                ]}
            />

            <ContainerTypeForm containerType={containerType} />
        </>
    );
}
