import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import { create as faqCreate, index as faqsIndex } from '@/routes/admin/faqs';
import type { FaqCategory } from '@/types/models';
import FaqForm from './faq-form';

type Props = {
    categories: FaqCategory[];
};

export default function FaqCreate({ categories }: Props) {
    return (
        <>
            <PageHeader
                title="Tambah FAQ"
                description="Satu pertanyaan beserta jawabannya untuk halaman FAQ publik."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'FAQ', href: faqsIndex() },
                    { title: 'Tambah FAQ', href: faqCreate() },
                ]}
            />

            <FaqForm faq={null} categories={categories} />
        </>
    );
}
