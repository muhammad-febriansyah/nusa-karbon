<?php

declare(strict_types=1);

/*
 * Locale konten.
 *
 * Untuk sekarang hanya Bahasa Indonesia atas permintaan klien. Kolom konten
 * tetap disimpan sebagai JSON translatable, jadi menambah bahasa lain nanti
 * cukup mengembalikan entri di bawah ini — tanpa migration dan tanpa mengubah
 * model, karena seluruh form dan validasi membaca daftar ini.
 *
 * Lihat docs/08-multilingual.md.
 */
return [
    'default' => 'id',

    'supported' => [
        'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'dir' => 'ltr', 'flag' => 'id'],
    ],

    /*
     * Bahasa yang direncanakan Fase 6.
     *
     * Sudah tampil di pemilih bahasa sebagai pilihan nonaktif, supaya pengunjung
     * melihat situs ini menuju multibahasa. Belum bisa dipilih karena rute
     * berprefix locale belum ada — lihat routes/web.php bagian yang menandai
     * pembungkusan grup prefix. Menautkannya sekarang berarti mengarahkan
     * pengunjung ke 404.
     *
     * Memindahkan satu entri dari sini ke `supported` sudah cukup untuk
     * mengaktifkannya; tidak ada tempat lain yang perlu disentuh.
     */
    'planned' => [
        'en' => ['name' => 'English', 'native' => 'English', 'dir' => 'ltr', 'flag' => 'gb'],
        'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'flag' => 'sa'],
        'ru' => ['name' => 'Russian', 'native' => 'Русский', 'dir' => 'ltr', 'flag' => 'ru'],
    ],
];
