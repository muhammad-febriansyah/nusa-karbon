import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as certificateCreate,
    index as certificatesIndex,
} from '@/routes/admin/certificates';
import CertificateForm from './certificate-form';

export default function CertificateCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Sertifikat"
                description="Isi identitas, masa berlaku, dan berkas sertifikat yang dipegang perusahaan."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Sertifikat', href: certificatesIndex() },
                    { title: 'Tambah Sertifikat', href: certificateCreate() },
                ]}
            />

            <CertificateForm certificate={null} />
        </>
    );
}
