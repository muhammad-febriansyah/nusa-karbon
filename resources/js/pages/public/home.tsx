import { setLayoutProps } from '@inertiajs/react';
import ContainerLoadBand from '@/components/public/home/container-load-band';
import CtaSection from '@/components/public/home/cta-section';
import FeatureSection from '@/components/public/home/feature-section';
import HeroSection from '@/components/public/home/hero-section';
import LatestPostSection from '@/components/public/home/latest-post-section';
import PackagingSection from '@/components/public/home/packaging-section';
import ProductSection from '@/components/public/home/product-section';
import ProductionStepSection from '@/components/public/home/production-step-section';
import ShippingPartnerSection from '@/components/public/home/shipping-partner-section';
import TrustBar from '@/components/public/home/trust-bar';
import SeoHead from '@/components/public/seo-head';
import { usePublicSettings } from '@/hooks/use-public-settings';
import type {
    PublicCertificate,
    PublicContainerLoad,
    PublicFeature,
    PublicPackagingOption,
    PublicPageBlock,
    PublicPostCard,
    PublicProductCard,
    PublicProductionStep,
    PublicShippingPartner,
    PublicSpecSheet,
} from '@/types';

type Props = {
    blocks: Record<string, PublicPageBlock>;
    products: PublicProductCard[];
    specSheet: PublicSpecSheet | null;
    containerLoad: PublicContainerLoad | null;
    features: PublicFeature[];
    productionSteps: PublicProductionStep[];
    packagingOptions: PublicPackagingOption[];
    shippingPartners: PublicShippingPartner[];
    trustBar: PublicCertificate[];
    latestPosts: PublicPostCard[];
};

/**
 * Beranda.
 *
 * Setiap bagian menghilangkan dirinya sendiri saat datanya kosong, jadi situs
 * yang isinya baru sebagian tetap terlihat utuh dan bukan seperti halaman yang
 * gagal dimuat.
 */
export default function Home({
    blocks,
    products,
    specSheet,
    containerLoad,
    features,
    productionSteps,
    packagingOptions,
    shippingPartners,
    trustBar,
    latestPosts,
}: Props) {
    const { text } = usePublicSettings();

    /*
     * Beranda membuka dengan pita gelap yang ditarik naik sampai ke belakang
     * navbar. Tanpa penanda ini navbar tetap memakai warna teks halaman terang
     * dan lenyap di atas pitanya.
     */
    setLayoutProps({ heroOnDarkPlate: true });

    return (
        <>
            <SeoHead
                title={
                    text('default_meta_title') ??
                    blocks.hero?.title ??
                    text('company_name') ??
                    'Beranda'
                }
                description={blocks.hero?.content ?? text('about_short')}
            />

            <HeroSection block={blocks.hero} specSheet={specSheet} />
            <ContainerLoadBand load={containerLoad} />
            <TrustBar certificates={trustBar} />
            <ProductSection items={products} />
            <FeatureSection items={features} />
            <ProductionStepSection items={productionSteps} />
            <PackagingSection items={packagingOptions} />
            <ShippingPartnerSection items={shippingPartners} />
            <LatestPostSection items={latestPosts} />
            <CtaSection block={blocks.cta} />
        </>
    );
}
