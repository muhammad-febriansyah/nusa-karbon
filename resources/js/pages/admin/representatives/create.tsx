import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as representativeCreate,
    index as representativesIndex,
} from '@/routes/admin/representatives';
import RepresentativeForm from './representative-form';

export default function RepresentativeCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Perwakilan"
                description="Isi identitas dan kontak resmi perwakilan yang berhak mewakili perusahaan."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Perwakilan Resmi', href: representativesIndex() },
                    {
                        title: 'Tambah Perwakilan',
                        href: representativeCreate(),
                    },
                ]}
            />

            <RepresentativeForm representative={null} />
        </>
    );
}
