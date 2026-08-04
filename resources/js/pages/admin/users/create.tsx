import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as userCreate,
    index as usersIndex,
} from '@/routes/admin/users';
import UserForm from './user-form';

export default function UserCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Pengguna"
                description="Buat akun baru; undangan pembuatan password dikirim otomatis ke emailnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Manajemen Pengguna', href: usersIndex() },
                    { title: 'Tambah Pengguna', href: userCreate() },
                ]}
            />

            <UserForm user={null} />
        </>
    );
}
