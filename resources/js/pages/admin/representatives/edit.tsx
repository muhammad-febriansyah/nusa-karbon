import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as representativeEdit,
    index as representativesIndex,
} from '@/routes/admin/representatives';
import type { Representative } from '@/types/models';
import RepresentativeForm from './representative-form';

type Props = {
    representative: Representative;
};

export default function RepresentativeEdit({ representative }: Props) {
    return (
        <>
            <PageHeader
                title={`Ubah ${representative.name}`}
                description="Perbarui kontak resmi dan masa tugas perwakilan ini. Setiap perubahan tercatat di activity log."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Perwakilan Resmi', href: representativesIndex() },
                    {
                        title: representative.name,
                        href: representativeEdit(representative.id),
                    },
                ]}
            />

            <RepresentativeForm representative={representative} />
        </>
    );
}
