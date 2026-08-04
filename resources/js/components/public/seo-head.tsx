import { Head, usePage } from '@inertiajs/react';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { localizedPath } from '@/lib/locale-url';

type BreadcrumbEntry = {
    name: string;
    /** Path relatif, misalnya `/products`. */
    path: string;
};

type Props = {
    title: string;
    description?: string | null;
    /** URL absolut gambar OG. Jatuh ke `default_og_image` bila kosong. */
    image?: string | null;
    type?: 'website' | 'article';
    /**
     * Remah roti tanpa entri "Beranda" — komponen ini yang menambahkannya
     * sebagai posisi pertama, supaya setiap halaman tidak mengulanginya.
     */
    breadcrumbs?: BreadcrumbEntry[];
    /** Memaksa `noindex` terlepas dari pengaturan `robots_index`. */
    noindex?: boolean;
};

/**
 * Tag SEO dan schema markup untuk halaman publik.
 *
 * JSON-LD sengaja dirender di body, bukan lewat `<Head>`. Inertia menyusun
 * `<head>` dengan manipulasi DOM dan tidak dapat diandalkan untuk isi
 * `<script>`; sementara schema.org menerima JSON-LD di mana pun dalam dokumen.
 *
 * @see docs/09-tasks.md bagian 5.1
 */
export default function SeoHead({
    title,
    description,
    image,
    type = 'website',
    breadcrumbs = [],
    noindex = false,
}: Props) {
    const page = usePage();
    const { text, number, boolean } = usePublicSettings();
    const { seo, locale, brand } = page.props;

    const baseUrl = seo?.baseUrl ?? '';
    const canonical = seo?.canonical ?? '';
    const path = seo?.path ?? '/';

    const siteName = brand?.name ?? text('company_name') ?? 'Nusacarbon';
    const metaDescription =
        description ?? text('default_meta_description') ?? text('about_short');
    const ogImage = image ?? text('default_og_image');
    const indexable = !noindex && boolean('robots_index');

    const alternates =
        locale.supported.length > 1
            ? locale.supported.map((item) => ({
                  code: item.code,
                  url: `${baseUrl}${localizedPath(path, item, locale.supported)}`,
              }))
            : [];

    /*
     * `x-default` menunjuk ke bahasa tanpa prefix, bukan ke entri pertama
     * daftar — urutan di `config/locales.php` tidak menentukan bahasa default.
     */
    const defaultLocale = locale.supported.find((item) => item.prefix === '');
    const defaultAlternate = alternates.find(
        (alternate) => alternate.code === defaultLocale?.code,
    );

    const organization = {
        '@context': 'https://schema.org',
        '@type': 'Organization',
        name: text('legal_name') ?? siteName,
        alternateName: siteName,
        url: baseUrl,
        ...(brand?.logo ? { logo: brand.logo } : {}),
        ...(text('about_short') ? { description: text('about_short') } : {}),
        ...(number('founded_year') !== null
            ? { foundingDate: String(number('founded_year')) }
            : {}),
        address: {
            '@type': 'PostalAddress',
            ...(text('address') ? { streetAddress: text('address') } : {}),
            ...(text('city') ? { addressLocality: text('city') } : {}),
            ...(text('province') ? { addressRegion: text('province') } : {}),
            ...(text('postal_code') ? { postalCode: text('postal_code') } : {}),
            addressCountry: text('country') ?? 'ID',
        },
        ...(text('phone') || text('email')
            ? {
                  contactPoint: {
                      '@type': 'ContactPoint',
                      contactType: 'sales',
                      ...(text('phone') ? { telephone: text('phone') } : {}),
                      ...(text('email') ? { email: text('email') } : {}),
                  },
              }
            : {}),
        sameAs: [
            'linkedin_url',
            'instagram_url',
            'facebook_url',
            'youtube_url',
            'tiktok_url',
            'x_url',
            'alibaba_url',
        ]
            .map((key) => text(key))
            .filter((url): url is string => url !== null),
    };

    const breadcrumbList = {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [{ name: 'Beranda', path: '/' }, ...breadcrumbs].map(
            (entry, index) => ({
                '@type': 'ListItem',
                position: index + 1,
                name: entry.name,
                item: `${baseUrl}${entry.path}`,
            }),
        ),
    };

    return (
        <>
            <Head title={title}>
                {metaDescription && (
                    <meta
                        head-key="description"
                        name="description"
                        content={metaDescription}
                    />
                )}
                <meta
                    head-key="robots"
                    name="robots"
                    content={indexable ? 'index, follow' : 'noindex, nofollow'}
                />
                <link head-key="canonical" rel="canonical" href={canonical} />

                <meta head-key="og:type" property="og:type" content={type} />
                <meta head-key="og:title" property="og:title" content={title} />
                <meta
                    head-key="og:site_name"
                    property="og:site_name"
                    content={siteName}
                />
                <meta head-key="og:url" property="og:url" content={canonical} />
                <meta
                    head-key="og:locale"
                    property="og:locale"
                    content={locale.current}
                />
                {metaDescription && (
                    <meta
                        head-key="og:description"
                        property="og:description"
                        content={metaDescription}
                    />
                )}
                {ogImage && (
                    <meta
                        head-key="og:image"
                        property="og:image"
                        content={ogImage}
                    />
                )}

                <meta
                    head-key="twitter:card"
                    name="twitter:card"
                    content={ogImage ? 'summary_large_image' : 'summary'}
                />

                {alternates.map((alternate) => (
                    <link
                        key={alternate.code}
                        head-key={`hreflang-${alternate.code}`}
                        rel="alternate"
                        hrefLang={alternate.code}
                        href={alternate.url}
                    />
                ))}
                {defaultAlternate && (
                    <link
                        head-key="hreflang-x-default"
                        rel="alternate"
                        hrefLang="x-default"
                        href={defaultAlternate.url}
                    />
                )}
            </Head>

            <script
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify(organization),
                }}
            />
            <script
                type="application/ld+json"
                dangerouslySetInnerHTML={{
                    __html: JSON.stringify(breadcrumbList),
                }}
            />
        </>
    );
}
