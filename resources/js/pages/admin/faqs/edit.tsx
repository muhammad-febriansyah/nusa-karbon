import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import { edit as faqEdit, index as faqsIndex } from '@/routes/admin/faqs';
import type { Faq, FaqCategory } from '@/types/models';
import FaqForm from './faq-form';

const LOCALE = 'id';

type Props = {
    faq: Faq;
    categories: FaqCategory[];
};

export default function FaqEdit({ faq, categories }: Props) {
    const label = faq.question?.[LOCALE] ?? 'FAQ';

    return (
        <>
            <PageHeader
                title="Ubah FAQ"
                description="Perbarui pertanyaan, jawaban, atau kategorinya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'FAQ', href: faqsIndex() },
                    { title: label, href: faqEdit(faq.id) },
                ]}
            />

            <FaqForm faq={faq} categories={categories} />
        </>
    );
}
