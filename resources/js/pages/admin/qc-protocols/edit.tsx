import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as itemEdit,
    index as itemsIndex,
} from '@/routes/admin/qc-protocols';
import type { QcProtocol } from '@/types/models';
import QcProtocolForm from './qc-protocol-form';

const LOCALE = 'id';

type Props = {
    item: QcProtocol;
};

export default function QcProtocolEdit({ item }: Props) {
    const label = item.title?.[LOCALE] ?? 'Protokol';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui penjelasan dan hasil uji protokol ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Protokol QC', href: itemsIndex() },
                    { title: label, href: itemEdit(item.id) },
                ]}
            />

            <QcProtocolForm item={item} />
        </>
    );
}
