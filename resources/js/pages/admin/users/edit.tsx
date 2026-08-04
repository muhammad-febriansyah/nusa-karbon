import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import { edit as userEdit, index as usersIndex } from '@/routes/admin/users';
import type { AdminUser } from '@/types/models';
import UserForm from './user-form';

type Props = {
    user: AdminUser;
};

export default function UserEdit({ user }: Props) {
    return (
        <>
            <PageHeader
                title={`Ubah ${user.name}`}
                description="Perbarui data akun. Password hanya bisa diganti pemiliknya lewat tautan reset."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Manajemen Pengguna', href: usersIndex() },
                    { title: user.name, href: userEdit(user.id) },
                ]}
            />

            <UserForm user={user} />
        </>
    );
}
