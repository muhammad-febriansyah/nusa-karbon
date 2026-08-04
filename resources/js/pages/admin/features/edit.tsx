import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import { edit as itemEdit, index as itemsIndex } from '@/routes/admin/features';
import type { Feature } from '@/types/models';
import FeatureForm from './feature-form';

const LOCALE = 'id';

type Props = {
    item: Feature;
};

export default function FeatureEdit({ item }: Props) {
    const label = item.title?.[LOCALE] ?? 'Keunggulan';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui judul, ikon, dan penjelasan keunggulan ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Keunggulan', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <FeatureForm item={item} />
        </>
    );
}
