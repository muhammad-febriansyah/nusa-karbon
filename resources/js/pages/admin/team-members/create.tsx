import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as itemCreate,
    index as itemsIndex,
} from '@/routes/admin/team-members';
import TeamMemberForm from './team-member-form';

export default function TeamMemberCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Anggota Tim"
                description="Nama, jabatan, dan foto anggota tim."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tim', href: itemsIndex() },
                    { title: 'Tambah Anggota Tim', href: itemCreate() },
                ]}
            />

            <TeamMemberForm item={null} />
        </>
    );
}
