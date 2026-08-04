import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    create as productCreate,
    index as productsIndex,
} from '@/routes/admin/products';
import type { SelectOption, SpecificationTemplateRow } from '@/types/models';
import ProductForm from './product-form';

type Props = {
    categories: SelectOption[];
    specificationTemplate: SpecificationTemplateRow[];
    documentTypes: SelectOption[];
    packagingOptions: SelectOption[];
};

export default function ProductCreate({
    categories,
    specificationTemplate,
    documentTypes,
    packagingOptions,
}: Props) {
    return (
        <>
            <PageHeader
                title="Tambah Produk"
                description="Isi identitas produk, data pengiriman, dan gambarnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Produk', href: productsIndex() },
                    { title: 'Tambah Produk', href: productCreate() },
                ]}
            />

            <ProductForm
                product={null}
                categories={categories}
                specificationTemplate={specificationTemplate}
                documentTypes={documentTypes}
                packagingOptions={packagingOptions}
            />
        </>
    );
}
