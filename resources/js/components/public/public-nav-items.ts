import type { NavbarItem } from '@/components/public/resizable-navbar';
import { about, containerCalculator, exportInfo, home } from '@/routes/public';
import blog from '@/routes/public/blog';
import products from '@/routes/public/products';

/**
 * Navigasi utama website publik.
 *
 * Dibatasi enam entri: pil navbar menyusut saat digulir, dan lebih dari ini
 * membuat tautan berdesakan pada lebar 1088px. "Kontak" tidak ikut karena
 * sudah diwakili tombol CTA di sebelah kanan navbar dan blok kontak di footer.
 *
 * Kalkulator Kontainer sengaja naik ke navigasi utama meski secara isi bagian
 * dari Info Ekspor — itu satu-satunya fitur yang tidak dimiliki kompetitor,
 * jadi tidak layak dikubur dua klik ke dalam.
 *
 * Urutannya mengikuti urutan pertanyaan pembeli, bukan struktur perusahaan:
 * jual apa, muat berapa, syaratnya apa, kamu siapa, buktinya mana. Karena itu
 * Produk memegang slot kedua — pembeli yang mendarat dari pencarian belum
 * peduli siapa penjualnya — dan Tentang Kami turun ke sebelah tombol penawaran,
 * tempat keraguan soal legalitas muncul persis sebelum tombolnya ditekan. Blog
 * paling belakang: pintu masuk dari pencarian, bukan tujuan orang yang sudah di
 * dalam.
 *
 * Label masih Bahasa Indonesia karena `config/locales.php` baru mengaktifkan
 * satu locale. Fase 6 memindahkannya ke berkas bahasa.
 */
export const publicNavItems: NavbarItem[] = [
    { name: 'Beranda', href: home() },
    { name: 'Produk', href: products.index() },
    { name: 'Kalkulator', href: containerCalculator() },
    { name: 'Info Ekspor', href: exportInfo() },
    { name: 'Tentang Kami', href: about() },
    { name: 'Blog', href: blog.index() },
];
