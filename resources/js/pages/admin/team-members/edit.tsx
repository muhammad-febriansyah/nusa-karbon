import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/team-members';
import type { TeamMember } from '@/types/models';
import TeamMemberForm from './team-member-form';

type Props = {
    item: TeamMember;
};

export default function TeamMemberEdit({ item }: Props) {
    const label = item.name;

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui jabatan, bio, dan foto anggota tim ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Tim', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <TeamMemberForm item={item} />
        </>
    );
}
