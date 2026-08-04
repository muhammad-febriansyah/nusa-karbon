import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/features';
import FeatureForm from './feature-form';

export default function FeatureCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Keunggulan"
                description="Satu poin keunggulan beserta ikon dan penjelasannya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Keunggulan', href: itemsIndex() },
                    { title: 'Tambah Keunggulan', href: itemCreate() },
                ]}
            />

            <FeatureForm item={null} />
        </>
    );
}
