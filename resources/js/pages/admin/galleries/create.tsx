import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as galleryCreate,
    index as galleriesIndex,
} from '@/routes/admin/galleries';
import GalleryForm from './gallery-form';

export default function GalleryCreate() {
    return (
        <>
            <PageHeader
                title="Tambah Album"
                description="Buat album lebih dulu, lalu unggah foto dan videonya di halaman berikutnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Galeri', href: galleriesIndex() },
                    { title: 'Tambah Album', href: galleryCreate() },
                ]}
            />

            <GalleryForm gallery={null} />
        </>
    );
}
