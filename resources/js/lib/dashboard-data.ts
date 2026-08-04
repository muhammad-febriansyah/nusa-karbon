import {
    AlertTriangle,
    Clock,
    FileText,
    Globe,
    Inbox,
    Package,
    TrendingUp,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

/**
 * Placeholder dashboard content taken from the admin panel template. Swap these
 * constants for Inertia props once the RFQ, product and article models exist.
 */

export type Stat = {
    label: string;
    value: string;
    icon: LucideIcon;
    trend?: string;
    trendIcon: LucideIcon;
    note?: string;
    highlighted?: boolean;
};

export const stats: Stat[] = [
    {
        label: 'RFQ Belum Dibaca',
        value: '7',
        icon: Inbox,
        trend: '+3 dari kemarin',
        trendIcon: TrendingUp,
        highlighted: true,
    },
    {
        label: 'RFQ Bulan Ini',
        value: '34',
        icon: TrendingUp,
        trend: '+18% vs bulan lalu',
        trendIcon: TrendingUp,
    },
    {
        label: 'Produk Aktif',
        value: '9',
        icon: Package,
        trendIcon: TrendingUp,
        note: 'dari 12 total',
    },
    {
        label: 'Artikel Terbit',
        value: '24',
        icon: FileText,
        trendIcon: TrendingUp,
        note: '2 terjadwal',
    },
];

export type MonthlyRfq = {
    month: string;
    height: number;
};

export const monthlyRfqs: MonthlyRfq[] = [
    { month: 'Jan', height: 58 },
    { month: 'Feb', height: 68 },
    { month: 'Mar', height: 50 },
    { month: 'Apr', height: 76 },
    { month: 'Mei', height: 62 },
    { month: 'Jun', height: 55 },
    { month: 'Jul', height: 71 },
    { month: 'Agu', height: 64 },
    { month: 'Sep', height: 82 },
    { month: 'Okt', height: 90 },
    { month: 'Nov', height: 74 },
    { month: 'Des', height: 100 },
];

export type Attention = {
    title: string;
    description: string;
    icon: LucideIcon;
    tone: 'warning' | 'info';
};

export const attentionItems: Attention[] = [
    {
        title: '2 produk belum punya data master carton',
        description: 'Tidak muncul di Container Calculator',
        icon: AlertTriangle,
        tone: 'warning',
    },
    {
        title: 'Sertifikat ISO 9001 kedaluwarsa 45 hari lagi',
        description: 'Perbarui sebelum 16 September 2026',
        icon: Clock,
        tone: 'warning',
    },
    {
        title: '12 konten belum diterjemahkan ke Bahasa Arab',
        description: 'Prioritas pasar Timur Tengah',
        icon: Globe,
        tone: 'info',
    },
];

export type RfqStatus = 'Baru' | 'Dihubungi' | 'Penawaran' | 'Menang';

export type Rfq = {
    company: string;
    country: string;
    product: string;
    incoterms: string;
    tonnage: string;
    status: RfqStatus;
};

export const recentRfqs: Rfq[] = [
    {
        company: 'Al Rashid Trading LLC',
        country: 'Uni Emirat Arab',
        product: 'Shisha Premium',
        incoterms: 'CIF',
        tonnage: '54 MT',
        status: 'Baru',
    },
    {
        company: 'Moskva Coal Import',
        country: 'Rusia',
        product: 'BBQ Hexagonal',
        incoterms: 'FOB',
        tonnage: '3 × 40HC',
        status: 'Dihubungi',
    },
    {
        company: 'Anadolu Kömür A.Ş.',
        country: 'Turki',
        product: 'Shisha Standard',
        incoterms: 'CIF',
        tonnage: '26 MT',
        status: 'Penawaran',
    },
    {
        company: 'Golden Flame GmbH',
        country: 'Jerman',
        product: 'Shisha Premium',
        incoterms: 'FOB',
        tonnage: '18 MT',
        status: 'Menang',
    },
    {
        company: 'Seoul Charcoal Co.',
        country: 'Korea Selatan',
        product: 'BBQ Hexagonal',
        incoterms: 'CIF',
        tonnage: '52 MT',
        status: 'Baru',
    },
    {
        company: 'Cairo Shisha Supply',
        country: 'Mesir',
        product: 'Shisha Premium',
        incoterms: 'FOB',
        tonnage: '78 MT',
        status: 'Dihubungi',
    },
];

/**
 * Badge memakai latar beropacity rendah dan teks berwarna penuh agar tetap
 * terbaca setelah tema diganti. Referensi: docs/05-design-system.md bagian 8.
 */
export const statusStyles: Record<RfqStatus, string> = {
    Baru: 'bg-primary/12 text-primary',
    Dihubungi: 'bg-info/12 text-info',
    Penawaran: 'bg-warning/12 text-warning',
    Menang: 'bg-success/12 text-success',
};

export type InquiryCountry = {
    name: string;
    share: number;
    bar: number;
    flag: string;
};

export const inquiryCountries: InquiryCountry[] = [
    {
        name: 'Uni Emirat Arab',
        share: 28,
        bar: 84,
        flag: 'linear-gradient(#00843D 33.3%,#fff 33.3% 66.6%,#000 66.6%)',
    },
    {
        name: 'Rusia',
        share: 19,
        bar: 57,
        flag: 'linear-gradient(#fff 33.3%,#0039A6 33.3% 66.6%,#D52B1E 66.6%)',
    },
    {
        name: 'Turki',
        share: 15,
        bar: 45,
        flag: 'radial-gradient(circle at 42% 50%,#fff 0 3px,rgba(255,255,255,0) 3px),#E30A17',
    },
    {
        name: 'Jerman',
        share: 11,
        bar: 33,
        flag: 'linear-gradient(#000 33.3%,#DD0000 33.3% 66.6%,#FFCE00 66.6%)',
    },
    {
        name: 'Mesir',
        share: 9,
        bar: 27,
        flag: 'linear-gradient(#CE1126 33.3%,#fff 33.3% 66.6%,#000 66.6%)',
    },
];
