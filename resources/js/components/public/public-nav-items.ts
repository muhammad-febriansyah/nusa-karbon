import type { NavbarItem } from '@/components/public/resizable-navbar';
import { about, containerCalculator, exportInfo } from '@/routes/public';
import blog from '@/routes/public/blog';
import products from '@/routes/public/products';

export type NavGroup = {
    /** Judul kelompok. Hanya tampil di menu ponsel. */
    name: string;
    items: NavbarItem[];
};

/**
 * Navigasi utama website publik, dibagi menurut pertanyaan yang dijawabnya.
 *
 * Kelompok pertama adalah jalur beli: jual apa, muat berapa, syaratnya apa.
 * Kelompok kedua untuk memeriksa penjualnya. Dua pertanyaan yang berbeda, jadi
 * dipisah garis di layar lebar dan diberi judul di menu ponsel — di daftar
 * menurun, judul lebih terbaca daripada garis.
 *
 * "Beranda" sengaja tidak ada. Logonya sudah menuju ke sana dan sudah bertanda
 * `aria-label="Beranda"`, jadi entri terpisah hanya tautan kembar yang memakan
 * slot paling terlihat di seluruh situs.
 *
 * "Kontak" juga tidak ikut: sudah diwakili tombol penawaran di sebelah kanan
 * navbar dan blok kontak di footer.
 *
 * Kalkulator Kontainer sengaja naik ke navigasi utama meski secara isi bagian
 * dari Info Ekspor — itu satu-satunya fitur yang tidak dimiliki kompetitor,
 * jadi tidak layak dikubur dua klik ke dalam.
 *
 * Label masih Bahasa Indonesia karena `config/locales.php` baru mengaktifkan
 * satu locale. Fase 6 memindahkannya ke berkas bahasa.
 */
export const publicNavGroups: NavGroup[] = [
    {
        name: 'Belanja',
        items: [
            { name: 'Produk', href: products.index() },
            { name: 'Kalkulator', href: containerCalculator() },
            { name: 'Info Ekspor', href: exportInfo() },
        ],
    },
    {
        name: 'Perusahaan',
        items: [
            { name: 'Tentang Kami', href: about() },
            { name: 'Blog', href: blog.index() },
        ],
    },
];

/** Seluruh entri tanpa kelompoknya, untuk pemakaian yang tidak memerlukannya. */
export const publicNavItems: NavbarItem[] = publicNavGroups.flatMap(
    (group) => group.items,
);
