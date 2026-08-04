import PageHeader from '@/components/shared/page-header';
import { dashboard } from '@/routes/admin';
import {
    edit as productEdit,
    index as productsIndex,
} from '@/routes/admin/products';
import type {
    Product,
    SelectOption,
    SpecificationTemplateRow,
} from '@/types/models';
import ProductForm from './product-form';

const LOCALE = 'id';

type Props = {
    product: Product;
    categories: SelectOption[];
    specificationTemplate: SpecificationTemplateRow[];
    documentTypes: SelectOption[];
    packagingOptions: SelectOption[];
};

export default function ProductEdit({
    product,
    categories,
    specificationTemplate,
    documentTypes,
    packagingOptions,
}: Props) {
    const name = product.name?.[LOCALE] ?? product.code;

    return (
        <>
            <PageHeader
                title={`Ubah ${name}`}
                description="Perbarui identitas produk, data pengiriman, dan gambarnya."
                breadcrumbs={[
                    { title: 'Dashboard', href: dashboard() },
                    { title: 'Produk', href: productsIndex() },
                    { title: name, href: productEdit(product.id) },
                ]}
            />

            <ProductForm
                product={product}
                categories={categories}
                specificationTemplate={specificationTemplate}
                documentTypes={documentTypes}
                packagingOptions={packagingOptions}
            />
        </>
    );
}
