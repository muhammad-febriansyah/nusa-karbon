import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as certificateEdit,
    index as certificatesIndex,
} from '@/routes/admin/certificates';
import type { Certificate } from '@/types/models';
import CertificateForm from './certificate-form';

const LOCALE = 'id';

type Props = {
    certificate: Certificate;
};

export default function CertificateEdit({ certificate }: Props) {
    const name = certificate.name?.[LOCALE] ?? 'Sertifikat';

    return (
        <>
            <PageHeader
                title={`Ubah ${name}`}
                description="Perbarui masa berlaku, berkas, dan tampilannya di halaman publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Sertifikat', href: certificatesIndex() },
                    { title: name, href: certificateEdit(certificate.id) },
                ]}
            />

            <CertificateForm certificate={certificate} />
        </>
    );
}
