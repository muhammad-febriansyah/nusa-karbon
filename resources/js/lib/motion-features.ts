import { domMax } from 'motion/react';

/**
 * Bundel fitur `motion` yang dimuat terpisah oleh `LazyMotion`.
 *
 * Berkas ini sengaja berdiri sendiri agar Vite memecahnya jadi chunk
 * tersendiri — fitur animasi baru diunduh setelah halaman tampil, bukan ikut
 * di bundel yang memblokir render pertama.
 *
 * `domMax` dipilih, bukan `domAnimation`, karena pil hover pada navbar publik
 * memakai `layoutId`. Animasi layout hanya ada di bundel ini.
 */
export default domMax;
