import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { google } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            // Semua font di-host lokal (WOFF2, font-display: swap), bukan lewat
            // Google Fonts CDN.
            //
            // Poppins tetap jadi teks umum situs. Archivo dan IBM Plex Mono
            // dipakai bagian teknis halaman publik: Archivo untuk judul dan
            // IBM Plex Mono untuk seluruh angka spesifikasi, yang butuh lebar
            // digit seragam agar kolomnya tidak bergeser antar produk.
            //
            // Yang di-preload hanya weight yang muncul di layar pertama.
            // Referensi: docs/05-design-system.md bagian 1.
            fonts: [
                google('Poppins', {
                    weights: [300, 400, 500, 600, 700],
                    preload: [{ weight: 400 }, { weight: 600 }],
                }),
                google('Archivo', {
                    weights: [400, 500, 600, 700],
                    preload: [{ weight: 700 }],
                }),
                google('IBM Plex Mono', {
                    weights: [400, 500, 600],
                    preload: [{ weight: 500 }],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    // ApexCharts memuat React lewat `prop-types`; tanpa dedupe, pra-bundel dev
    // membuat salinan React kedua dan hook di dalam grafik gagal.
    resolve: {
        dedupe: ['react', 'react-dom'],
    },
    optimizeDeps: {
        include: ['react', 'react-dom', 'apexcharts', 'react-apexcharts/core'],
    },
});
