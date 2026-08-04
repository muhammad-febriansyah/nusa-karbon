import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as galleryEdit,
    index as galleriesIndex,
} from '@/routes/admin/galleries';
import type { Gallery } from '@/types/models';
import GalleryForm from './gallery-form';

const LOCALE = 'id';

type Props = {
    gallery: Gallery;
};

export default function GalleryEdit({ gallery }: Props) {
    const label = gallery.title?.[LOCALE] ?? 'Album';

    return (
        <>
            <PageHeader
                title={`Ubah ${label}`}
                description="Perbarui judul, deskripsi, dan sampul album ini."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Galeri', href: galleriesIndex() },
                    { title: label, href: galleryEdit(gallery.id) },
                ]}
            />

            <GalleryForm gallery={gallery} />
        </>
    );
}
