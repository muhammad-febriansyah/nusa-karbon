import {
    Award,
    Boxes,
    Building2,
    Container,
    FileText,
    FolderOpen,
    HelpCircle,
    History,
    Image,
    Inbox,
    LayoutDashboard,
    LayoutTemplate,
    Layers,
    MessageSquare,
    Package,
    Palette,
    ShieldCheck,
    Ship,
    Settings,
    Sparkles,
    Tags,
    UserCog,
    UserCheck,
    Users,
} from 'lucide-react';
import { dashboard } from '@/routes/admin';
import * as activityLog from '@/routes/admin/activity-log';
import * as certificates from '@/routes/admin/certificates';
import * as contactMessages from '@/routes/admin/contact-messages';
import * as containerTypes from '@/routes/admin/container-types';
import * as faqCategories from '@/routes/admin/faq-categories';
import * as faqs from '@/routes/admin/faqs';
import * as features from '@/routes/admin/features';
import * as galleries from '@/routes/admin/galleries';
import * as media from '@/routes/admin/media';
import * as packagingOptions from '@/routes/admin/packaging-options';
import * as pageBlocks from '@/routes/admin/page-blocks';
import * as postCategories from '@/routes/admin/post-categories';
import * as posts from '@/routes/admin/posts';
import * as productCategories from '@/routes/admin/product-categories';
import * as productionSteps from '@/routes/admin/production-steps';
import * as products from '@/routes/admin/products';
import * as qcProtocols from '@/routes/admin/qc-protocols';
import * as representatives from '@/routes/admin/representatives';
import * as rfqs from '@/routes/admin/rfqs';
import * as shippingPartners from '@/routes/admin/shipping-partners';
import * as siteSettings from '@/routes/admin/site-settings';
import * as teamMembers from '@/routes/admin/team-members';
import * as themes from '@/routes/admin/themes';
import * as users from '@/routes/admin/users';
import type { AdminNavGroup } from '@/types';

/**
 * Sidebar structure from the Nusacarbon admin panel template. Entries with a
 * `null` href are placeholders for screens that have not been built yet.
 */
export const adminNavGroups: AdminNavGroup[] = [
    {
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutDashboard,
            },
        ],
    },
    {
        label: 'Produk',
        items: [
            { title: 'Produk', href: products.index(), icon: Package },
            {
                title: 'Kategori Produk',
                href: productCategories.index(),
                icon: Layers,
            },
            {
                title: 'Tipe Kontainer',
                href: containerTypes.index(),
                icon: Container,
            },
            {
                title: 'Opsi Kemasan',
                href: packagingOptions.index(),
                icon: Boxes,
            },
        ],
    },
    {
        label: 'Perusahaan',
        items: [
            { title: 'Sertifikat', href: certificates.index(), icon: Award },
            {
                title: 'Proses Produksi',
                href: productionSteps.index(),
                icon: Building2,
            },
            {
                title: 'Protokol QC',
                href: qcProtocols.index(),
                icon: ShieldCheck,
            },
            { title: 'Keunggulan', href: features.index(), icon: Sparkles },
            { title: 'Tim', href: teamMembers.index(), icon: Users },
            {
                title: 'Mitra Pelayaran',
                href: shippingPartners.index(),
                icon: Ship,
            },
            {
                title: 'Perwakilan Resmi',
                href: representatives.index(),
                icon: UserCheck,
            },
        ],
    },
    {
        label: 'Konten',
        items: [
            { title: 'Artikel', href: posts.index(), icon: FileText },
            {
                title: 'Kategori Artikel',
                href: postCategories.index(),
                icon: Layers,
            },
            { title: 'FAQ', href: faqs.index(), icon: HelpCircle },
            {
                title: 'Kategori FAQ',
                href: faqCategories.index(),
                icon: Tags,
            },
            { title: 'Galeri', href: galleries.index(), icon: Image },
            {
                title: 'Blok Halaman',
                href: pageBlocks.index(),
                icon: LayoutTemplate,
            },
        ],
    },
    {
        label: 'Leads',
        items: [
            {
                title: 'RFQ Inbox',
                href: rfqs.index(),
                icon: Inbox,
                badgeKey: 'rfq',
            },
            {
                title: 'Pesan Kontak',
                href: contactMessages.index(),
                icon: MessageSquare,
                badgeKey: 'contactMessages',
            },
        ],
    },
    {
        label: 'Sistem',
        items: [
            { title: 'Media Library', href: media.index(), icon: FolderOpen },
            {
                title: 'Pengaturan Situs',
                href: siteSettings.index(),
                icon: Settings,
            },
            { title: 'Tema & Warna', href: themes.index(), icon: Palette },
            {
                title: 'Manajemen Pengguna',
                href: users.index(),
                icon: UserCog,
            },
            {
                title: 'Log Aktivitas',
                href: activityLog.index(),
                icon: History,
            },
        ],
    },
];
