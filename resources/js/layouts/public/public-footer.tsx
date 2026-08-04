import { Link } from '@inertiajs/react';
import type { InertiaLinkProps } from '@inertiajs/react';
import {
    ArrowRight,
    Clock,
    Facebook,
    Instagram,
    Linkedin,
    Mail,
    MapPin,
    MessageCircle,
    Music2,
    Phone,
    ShieldCheck,
    Store,
    Twitter,
    Youtube,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import BrandMark from '@/components/brand-mark';
import { usePublicSettings } from '@/hooks/use-public-settings';
import { toUrl } from '@/lib/utils';
import {
    about,
    containerCalculator,
    contact,
    exportInfo,
    verifyRepresentative,
} from '@/routes/public';
import blog from '@/routes/public/blog';
import products from '@/routes/public/products';

/** Kunci pengaturan sosial beserta ikonnya, mengikuti urutan tampil. */
const SOCIAL_LINKS: { key: string; label: string; icon: LucideIcon }[] = [
    { key: 'linkedin_url', label: 'LinkedIn', icon: Linkedin },
    { key: 'instagram_url', label: 'Instagram', icon: Instagram },
    { key: 'facebook_url', label: 'Facebook', icon: Facebook },
    { key: 'youtube_url', label: 'YouTube', icon: Youtube },
    { key: 'tiktok_url', label: 'TikTok', icon: Music2 },
    { key: 'x_url', label: 'X', icon: Twitter },
    { key: 'alibaba_url', label: 'Alibaba', icon: Store },
];

type FooterLink = { name: string; href: NonNullable<InertiaLinkProps['href']> };

/**
 * Navigasi footer dipecah menurut niat pengunjung, bukan disalin dari navbar.
 *
 * Sebelumnya seluruh tautan ditumpuk dalam satu kolom "Jelajahi" berisi delapan
 * baris, sementara dua kolom di sebelahnya hanya berisi satu sampai dua baris.
 * Kolom itu sendirian yang menentukan tinggi footer, dan sisanya jadi ruang
 * kosong. Dua kelompok yang lebih pendek membuat tingginya seimbang sekaligus
 * memberi tahu isi tiap tautan sebelum diklik.
 */
const COMPANY_LINKS: FooterLink[] = [
    { name: 'Tentang Kami', href: about() },
    { name: 'Info Ekspor', href: exportInfo() },
    { name: 'Blog', href: blog.index() },
    { name: 'Kontak', href: contact() },
];

const CATALOGUE_LINKS: FooterLink[] = [
    { name: 'Katalog Produk', href: products.index() },
    { name: 'Kalkulator Kontainer', href: containerCalculator() },
    { name: 'Verifikasi Perwakilan', href: verifyRepresentative() },
];

function FooterHeading({ children }: { children: ReactNode }) {
    return (
        <h2 className="text-sm font-semibold tracking-tight text-foreground">
            {children}
        </h2>
    );
}

function FooterNav({ title, links }: { title: string; links: FooterLink[] }) {
    return (
        <nav aria-label={title}>
            <FooterHeading>{title}</FooterHeading>
            <ul className="mt-4 space-y-2.5">
                {links.map((item) => (
                    <li key={toUrl(item.href)}>
                        <Link
                            href={item.href}
                            className="text-sm text-muted-foreground underline-offset-4 transition-colors hover:text-primary hover:underline"
                        >
                            {item.name}
                        </Link>
                    </li>
                ))}
            </ul>
        </nav>
    );
}

/**
 * Satu baris kontak. Tidak dirender sama sekali bila nilainya kosong — footer
 * dengan label "Telepon" tanpa nomor terlihat seperti situs yang rusak.
 */
function ContactRow({
    icon: Icon,
    children,
}: {
    icon: LucideIcon;
    children: ReactNode;
}) {
    return (
        <li className="flex items-start gap-2.5 text-sm text-muted-foreground">
            <Icon className="mt-0.5 size-4 shrink-0 text-primary" />
            <span className="min-w-0">{children}</span>
        </li>
    );
}

export default function PublicFooter() {
    const { text } = usePublicSettings();

    const address = text('address');
    const city = text('city');
    const province = text('province');
    const postalCode = text('postal_code');
    const country = text('country');
    const phone = text('phone');
    const whatsapp = text('whatsapp');
    const email = text('email');
    const officeHours = text('office_hours');
    const legalName = text('legal_name') ?? text('company_name');

    const region = [city, province, postalCode, country]
        .filter((part): part is string => part !== null)
        .join(', ');

    /*
     * Nomor identitas usaha. Ditampilkan berdampingan di bawah profil singkat,
     * bukan sebagai kolom tersendiri — satu nomor HS Code tidak cukup untuk
     * mengisi seperempat lebar footer, dan kolom yang isinya satu baris justru
     * membuat sisanya terlihat bolong.
     */
    const identifiers = [
        { label: 'HS Code', value: text('hs_code') },
        { label: 'NIB', value: text('nib') },
        { label: 'NPWP', value: text('npwp') },
        { label: 'No. Eksportir', value: text('export_id') },
    ].filter(
        (item): item is { label: string; value: string } => item.value !== null,
    );

    const socials = SOCIAL_LINKS.map((social) => ({
        ...social,
        url: text(social.key),
    })).filter((social) => social.url !== null);

    return (
        <footer className="mt-24 border-t border-border/70 bg-card">
            <div className="mx-auto grid w-full max-w-7xl gap-10 px-6 py-14 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">
                <div className="lg:col-span-4">
                    <BrandMark
                        imageClassName="h-8"
                        labelClassName="text-lg font-bold tracking-tight"
                    />

                    {text('about_short') && (
                        <p className="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground">
                            {text('about_short')}
                        </p>
                    )}

                    {identifiers.length > 0 && (
                        <dl className="mt-6 flex flex-wrap gap-x-6 gap-y-3">
                            {identifiers.map((item) => (
                                <div key={item.label}>
                                    <dt className="text-xs text-muted-foreground">
                                        {item.label}
                                    </dt>
                                    <dd
                                        className="mt-0.5 font-mono text-sm tabular-nums"
                                        dir="ltr"
                                    >
                                        {item.value}
                                    </dd>
                                </div>
                            ))}
                        </dl>
                    )}

                    {socials.length > 0 && (
                        <ul className="mt-6 flex flex-wrap gap-2">
                            {socials.map((social) => (
                                <li key={social.key}>
                                    <a
                                        href={social.url ?? '#'}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label={social.label}
                                        className="flex size-9 items-center justify-center rounded-[var(--radius)] border border-border/70 text-muted-foreground transition-colors hover:border-primary hover:text-primary"
                                    >
                                        <social.icon className="size-4" />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="lg:col-span-2">
                    <FooterNav title="Perusahaan" links={COMPANY_LINKS} />
                </div>

                <div className="lg:col-span-3">
                    <FooterNav title="Produk & Alat" links={CATALOGUE_LINKS} />
                </div>

                <div className="lg:col-span-3">
                    <FooterHeading>Hubungi Kami</FooterHeading>

                    <ul className="mt-4 space-y-3">
                        {(address !== null || region !== '') && (
                            <ContactRow icon={MapPin}>
                                {address && <span>{address}</span>}
                                {address && region !== '' && <br />}
                                {region !== '' && <span>{region}</span>}
                            </ContactRow>
                        )}

                        {phone && (
                            <ContactRow icon={Phone}>
                                <a
                                    href={`tel:${phone.replace(/\s/g, '')}`}
                                    dir="ltr"
                                    className="transition-colors hover:text-primary"
                                >
                                    {phone}
                                </a>
                            </ContactRow>
                        )}

                        {whatsapp && (
                            <ContactRow icon={MessageCircle}>
                                <a
                                    href={`https://wa.me/${whatsapp.replace(/\D/g, '')}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    dir="ltr"
                                    className="transition-colors hover:text-primary"
                                >
                                    {whatsapp}
                                </a>
                            </ContactRow>
                        )}

                        {email && (
                            <ContactRow icon={Mail}>
                                <a
                                    href={`mailto:${email}`}
                                    dir="ltr"
                                    className="break-all transition-colors hover:text-primary"
                                >
                                    {email}
                                </a>
                            </ContactRow>
                        )}

                        {officeHours && (
                            <ContactRow icon={Clock}>{officeHours}</ContactRow>
                        )}
                    </ul>

                    {/*
                     * Selalu ada, tidak bergantung pengaturan mana pun.
                     *
                     * Telepon, WhatsApp, dan email boleh saja belum diisi admin,
                     * tetapi kolom kontak yang isinya cuma jam kerja tidak
                     * memberi pengunjung satu pun cara menghubungi. Formulir
                     * penawaran selalu tersedia, jadi itu yang dijadikan
                     * jaminannya.
                     */}
                    <Link
                        href={contact()}
                        className="group mt-6 inline-flex items-center gap-2 text-sm font-medium text-primary underline-offset-4 transition-colors hover:underline"
                    >
                        Kirim permintaan penawaran
                        <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5" />
                    </Link>
                </div>
            </div>

            <div className="border-t border-border/70">
                <div className="mx-auto flex w-full max-w-7xl flex-col gap-3 px-6 py-5 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        <span dir="ltr">&copy; {new Date().getFullYear()}</span>{' '}
                        {legalName}. Seluruh hak cipta dilindungi.
                    </p>

                    {/*
                     * Peringatan penipuan, bukan hiasan. Pemasok briket sering
                     * ditiru akun yang mengaku perwakilan resmi lalu meminta
                     * transfer ke rekening pribadi, jadi peringatannya diberi
                     * ikon dan ditaruh di setiap halaman.
                     */}
                    <p className="flex items-start gap-2 sm:items-center">
                        <ShieldCheck className="mt-0.5 size-4 shrink-0 text-primary sm:mt-0" />
                        <span>
                            Kami tidak pernah meminta pembayaran ke rekening
                            pribadi.{' '}
                            <Link
                                href={verifyRepresentative()}
                                className="font-medium text-primary underline-offset-4 hover:underline"
                            >
                                Verifikasi perwakilan resmi
                            </Link>
                        </span>
                    </p>
                </div>
            </div>
        </footer>
    );
}
