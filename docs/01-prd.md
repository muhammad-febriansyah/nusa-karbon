# 01 — Product Requirements Document

## 1. Latar Belakang

Klien adalah perusahaan trader / supply chain partner untuk briket arang batok kelapa (coconut shell charcoal briquettes) yang menjual ke pasar ekspor, terutama Timur Tengah, Eropa Timur, dan Asia.

Klien **tidak memiliki pabrik sendiri**. Produksi dilakukan oleh fabrikator mitra di bawah perjanjian pengadaan (Supply Agreement). Ini adalah fakta yang menentukan seluruh strategi konten: website tidak boleh mengklaim kepemilikan pabrik, dan tidak boleh memakai dokumentasi visual pihak lain tanpa izin resmi.

Posisi yang diambil: **Supply Chain & Export Management Company** — perusahaan yang melakukan sourcing dari fabrikator terverifikasi, menjalankan Quality Control independen, dan mengurus dokumentasi ekspor sampai barang naik kapal.

### Masalah yang dipecahkan

| Masalah | Dampak |
|---------|--------|
| Buyer internasional sulit memverifikasi kredibilitas eksportir Indonesia | Inquiry masuk sedikit, konversi rendah |
| Penipuan mengatasnamakan eksportir marak di industri ini | Buyer ragu transfer deposit |
| Buyer butuh angka teknis (spesifikasi, kapasitas kontainer) sebelum minta harga | Bolak-balik email, siklus penjualan panjang |
| Konten hanya Bahasa Inggris | Kehilangan pasar Timur Tengah & Rusia |
| Klien tidak bisa update konten sendiri | Bergantung pada developer, konten basi |

---

## 2. Tujuan Produk

### Tujuan Bisnis

1. Menghasilkan inquiry B2B berkualitas (lengkap dengan port tujuan, incoterms, tonase) minimal 20 per bulan dalam 6 bulan pertama.
2. Memperpendek siklus dari "buyer menemukan website" ke "buyer mengirim RFQ" dengan menyediakan data teknis secara terbuka tanpa gate.
3. Menurunkan insiden penipuan yang mengatasnamakan perusahaan melalui halaman verifikasi perwakilan resmi.
4. Menjangkau pasar berbahasa Arab dan Rusia dengan konten native, bukan terjemahan mesin.

### Tujuan Produk

1. Website B2B yang membangun kepercayaan melalui **bukti**, bukan klaim — sertifikat yang bisa diunduh, hasil uji lab, daftar perwakilan resmi.
2. Admin panel yang memungkinkan klien mengelola seluruh konten tanpa developer.
3. Skor Google PageSpeed > 90 di mobile dan desktop.
4. Struktur SEO multi-bahasa yang benar (hreflang, schema markup, sitemap per locale).

### Non-Goals (Tidak Dikerjakan)

- E-commerce / checkout online — bisnis ini B2B kontrak, bukan transaksi web.
- Akun buyer / login publik — seluruh halaman publik terbuka.
- Sistem manajemen inventori atau produksi.
- Integrasi ERP atau accounting.
- Aplikasi mobile.
- Live chat berbasis agen (cukup tombol WhatsApp).
- Dark mode atau toggle terang/gelap.
- Multi-role dan sistem perizinan granular — hanya satu role `admin`.

---

## 3. Aktor

| Aktor | Akses | Autentikasi |
|-------|-------|-------------|
| **Buyer / Pengunjung** | Seluruh halaman publik | Tidak ada |
| **Admin** | Seluruh admin panel | Fortify + 2FA opsional |

Hanya ada **satu role**: `admin`. Paket `spatie/laravel-permission` tetap dipasang dan di-seed dengan satu role, sehingga penambahan role di masa depan tidak memerlukan refactor lapisan otorisasi.

---

## 4. Ruang Lingkup Modul

### 4.1 Admin Panel (Prioritas Utama)

| Modul | Deskripsi |
|-------|-----------|
| **Autentikasi** | Login, lupa password, reset password, 2FA opsional, profil, ganti password |
| **Dashboard** | Ringkasan RFQ baru, statistik inquiry per negara, RFQ terbaru, shortcut aksi |
| **Manajemen Produk** | CRUD produk, spesifikasi teknis dinamis, dokumen (COA/MSDS/SHT), galeri, data pengiriman untuk kalkulator |
| **Kategori Produk** | CRUD kategori (master data) |
| **Tipe Kontainer** | CRUD tipe kontainer beserta kapasitas berat & volume |
| **Sertifikat** | CRUD sertifikat & legalitas, dengan logo dan file |
| **Opsi Kemasan** | CRUD packaging option (Bulk, Bulk Loose, Full Packaging) |
| **Proses Produksi** | CRUD langkah proses produksi (urut) |
| **Protokol QC** | CRUD jenis uji QC beserta target hasil |
| **Perwakilan Resmi** | CRUD daftar perwakilan terverifikasi — **wajib audit log** |
| **Tim** | CRUD anggota tim |
| **Mitra Pelayaran** | CRUD logo shipping line |
| **Keunggulan** | CRUD poin "Why Choose Us" dengan ikon |
| **Galeri** | CRUD album dan item galeri |
| **Blog** | CRUD artikel + kategori artikel, status draft/published, penjadwalan |
| **FAQ** | CRUD pertanyaan & jawaban, dikelompokkan per kategori |
| **Halaman Statis** | CRUD blok konten untuk halaman About & Export Info |
| **RFQ Inbox** | Daftar, detail, ubah status, catatan tindak lanjut, ekspor CSV |
| **Pesan Kontak** | Daftar dan detail pesan dari form kontak umum |
| **Kategori Artikel** | CRUD kategori artikel (master data) |
| **Pengaturan Situs** | 59 kunci dalam 6 grup: identitas, kontak, sosial media, SEO, email, sistem |
| **Tema & Warna** | Kustomisasi palet warna admin panel dan website publik, dengan pemeriksaan kontras |
| **Manajemen Pengguna** | CRUD pengguna admin + halaman detail, avatar, undangan, reset password |
| **Media Library** | Pustaka file terpusat, pencarian, hapus, lihat penggunaan |
| **Log Aktivitas** | Riwayat perubahan data penting |

Total **24 item menu** di sidebar. Grup PERUSAHAAN dan KONTEN bersifat collapsible dan tertutup secara default, terbuka otomatis bila halaman aktif berada di dalamnya.

### 4.2 Website Publik

| Halaman | Konten |
|---------|--------|
| **Home** | Hero (video/gambar), trust badges, ringkasan produk, keunggulan, opsi kemasan, mitra pelayaran, artikel terbaru, CTA RFQ |
| **About Us** | Profil perusahaan, tim, posisi dalam rantai pasok, legalitas & sertifikat, fasilitas QC, galeri |
| **Products** | Index produk + halaman detail per produk (tabel spesifikasi, unduh dokumen, galeri, kemasan) |
| **Export Info** | Proses produksi, MOQ & termin pembayaran, termin pengiriman, opsi kemasan, **Container Calculator**, FAQ |
| **News** | Index artikel + halaman detail artikel |
| **Contact** | Form RFQ, form kontak umum, peta, info kontak, **Verify Our Representative** |

---

## 5. Fitur Kunci — Rincian

### 5.1 Interactive Product Specs

Tabel spesifikasi dinamis per produk. Parameter tidak di-hardcode — admin bisa menambah, mengurangi, dan mengurutkan parameter per produk.

Parameter standar: Fixed Carbon, Moisture Content, Ash Content, Ash Colour, Volatile Matter, Calorific Value, Burning Time, Sulphur, Shape & Size.

Setiap produk punya tombol unduh langsung untuk COA, MSDS, dan SHT. **Tanpa email gate** — buyer B2B menganggap gate sebagai sinyal ketidakpercayaan.

### 5.2 Container Calculator

Buyer memilih produk dan tipe kontainer, sistem menghitung:

```
cartonByWeight = floor(container.max_payload_kg / product.master_carton_weight_kg)
cartonByVolume = floor(container.max_cbm / product.master_carton_cbm)
cartons        = min(cartonByWeight, cartonByVolume)

netWeight      = cartons × product.master_carton_weight_kg
grossWeight    = netWeight + (cartons × product.master_carton_tare_kg)
volumeUsed     = cartons × product.master_carton_cbm
limitedBy      = cartonByWeight <= cartonByVolume ? 'weight' : 'volume'
```

Hasil ditampilkan bersama **visualisasi SVG kontainer** yang terisi sesuai rasio pemakaian, dan label yang menyebutkan batasan mana yang mengunci (berat atau volume). Menampilkan batasan yang mengikat adalah pembeda utama — ini yang membuat buyer percaya angkanya bukan karangan.

Semua angka bersifat indikatif. Wajib ada disclaimer bahwa packing list final dikonfirmasi setelah PO.

**Dependensi data:** fitur ini tidak dapat diselesaikan tanpa berat dan volume master carton per produk dari klien.

### 5.3 RFQ & Anti-Scam

Form RFQ berisi field khusus perdagangan internasional:

- Nama perusahaan, negara, nama kontak
- Email bisnis, nomor WhatsApp
- Produk yang diminta
- Incoterms (FOB / CIF / CFR)
- Port of Destination
- Estimasi tonase per bulan
- Catatan kemasan / private label

Setiap submission mencatat locale, IP, dan user agent untuk analisis asal traffic.

Halaman **Verify Our Authorized Representative** menampilkan daftar nama, jabatan, wilayah, foto, dan kontak resmi. Disertai peringatan eksplisit: perusahaan tidak pernah meminta pembayaran ke rekening pribadi, dan tidak pernah memberi penawaran dari alamat email gratisan.

Karena halaman ini adalah alat pencegah penipuan, perubahan data perwakilan **wajib tercatat di log aktivitas** dengan informasi siapa dan kapan.

### 5.4 Multi-Bahasa

Tiga bahasa: **Inggris (default), Arab, Rusia**.

- URL berbasis prefix: `/`, `/ar/`, `/ru/` — **bukan** widget Google Translate.
- Bahasa Arab menggunakan layout **RTL** penuh (mirror layout, bukan sekadar teks kanan).
- Setiap halaman punya tag `hreflang` yang benar termasuk `x-default`.
- Sitemap terpisah per locale.
- Istilah teknis perdagangan (Incoterms, HS Code, nama parameter uji) tidak diterjemahkan — tetap dalam bahasa Inggris sesuai standar internasional.

Detail teknis: lihat [08-multilingual.md](./08-multilingual.md).

### 5.5 Tema yang Dapat Dikustomisasi

Admin dapat mengubah palet warna admin panel dan website publik tanpa bantuan developer dan tanpa build ulang.

- Lima preset bawaan yang tidak dapat dihapus; admin menduplikasi untuk membuat versi yang bisa diedit
- Tema aktif terpisah untuk admin dan publik
- Editor dengan pratinjau langsung yang merender komponen nyata
- Pemeriksaan kontras otomatis; kombinasi di bawah rasio 4,5:1 ditolak saat disimpan
- Tidak ada dark mode — tema adalah palet warna, bukan mode terang/gelap

Detail teknis: lihat [10-theming.md](./10-theming.md).

### 5.6 Upload File & Gambar

Wajib modern. Detail implementasi ada di [07-admin-ui-standards.md](./07-admin-ui-standards.md), ringkasnya:

- Drag & drop area dengan preview instan
- Upload asinkron dengan progress bar per file
- Validasi tipe & ukuran di sisi klien sebelum request dikirim
- Crop / reposisi untuk gambar yang butuh rasio tertentu
- Multi-upload dengan pengurutan drag-and-drop untuk galeri
- Konversi otomatis ke WebP + varian responsif
- Hapus file lama otomatis saat diganti

---

## 6. Non-Functional Requirements

### Performa

| Metrik | Target |
|--------|--------|
| Google PageSpeed (mobile) | > 90 |
| Google PageSpeed (desktop) | > 95 |
| LCP | < 2.0 detik |
| CLS | < 0.1 |
| INP | < 200 ms |
| TTFB | < 600 ms |

Strategi: Inertia SSR, image conversion ke WebP/AVIF, `loading="lazy"` di luar viewport, preload hero image, CDN Cloudflare, cache response publik, defer skrip non-kritis.

**Video hero:** jangan autoload di mobile. Gunakan poster image sebagai LCP element, video dimuat setelah interaksi atau hanya di desktop dengan koneksi memadai. Ini penyebab paling umum skor PageSpeed jeblok di website seperti ini.

### Keamanan

- Rate limiting pada form RFQ dan kontak (maksimal 5 submission per IP per jam)
- Honeypot field + verifikasi Cloudflare Turnstile pada form publik
- Validasi ketat semua input, sanitasi konten rich text (HTML Purifier)
- File upload: whitelist MIME type, pemeriksaan ekstensi ganda, simpan di luar public root, akses lewat signed URL untuk dokumen sensitif
- Password policy minimal 12 karakter, 2FA tersedia via Fortify
- Header keamanan: CSP, HSTS, X-Frame-Options, X-Content-Type-Options
- Audit log untuk perubahan pada `representatives`, `certificates`, `site_settings`, dan `users`

### Aksesibilitas

- Kontras teks minimal WCAG AA (4.5:1 untuk teks normal)
- Fokus keyboard terlihat jelas di semua elemen interaktif
- Semua gambar punya `alt` yang bermakna
- Form punya label eksplisit, bukan hanya placeholder
- `prefers-reduced-motion` dihormati

### Kompatibilitas

- Browser: 2 versi terakhir Chrome, Firefox, Safari, Edge
- Mobile: iOS Safari 15+, Chrome Android
- Responsif dari 360px sampai 1920px

### Backup & Pemeliharaan

- Backup database harian, retensi 30 hari
- Backup file media mingguan
- Log error terpantau (Laravel Pail saat development, log driver produksi terpisah)

---

## 7. Asumsi & Risiko

| Asumsi | Risiko jika salah |
|--------|-------------------|
| Klien menyediakan data teknis master carton | Container Calculator tidak bisa diselesaikan |
| Klien menyediakan penerjemah Arab & Rusia | Konten multi-bahasa kosong saat launch |
| Klien memiliki izin resmi untuk semua foto/video yang diberikan | Risiko gugatan hak cipta dan hilangnya kredibilitas |
| Klien memiliki file PDF COA/MSDS/SHT | Fitur unduh dokumen tidak berfungsi |
| Sample Djavacoal adalah referensi tampilan, bukan referensi fitur | Scope creep signifikan |

**Catatan penting:** poin terakhir wajib dikonfirmasi tertulis sebelum pengerjaan dimulai. Brief memuat 3 fitur yang tidak ada di sample (Container Calculator, form RFQ, Verify Representative), sementara sample memuat 5 hal yang tidak ada di brief (blog, MOQ terms, shipment terms, packaging options, FAQ). Dokumen ini mengasumsikan **keduanya masuk scope**.

---

## 8. Kriteria Selesai

Proyek dinyatakan selesai bila:

- [ ] Seluruh modul admin panel berfungsi dengan CRUD lengkap dan validasi
- [ ] Seluruh halaman publik tampil dengan konten dari database
- [ ] Tiga bahasa berfungsi termasuk RTL untuk Arab
- [ ] Container Calculator menghitung dengan benar untuk semua kombinasi produk × kontainer
- [ ] Form RFQ masuk ke database dan mengirim notifikasi email
- [ ] Skor PageSpeed memenuhi target di halaman Home, Product Detail, dan Blog Detail
- [ ] `./vendor/bin/pint --test` lolos tanpa error
- [ ] `./vendor/bin/phpstan analyse` lolos di level yang disepakati
- [ ] `./vendor/bin/pest` lolos seluruh test
- [ ] Tema dapat diganti dari admin panel tanpa build ulang, tanpa kedipan warna saat load
- [ ] Dokumentasi deployment dan panduan admin diserahkan
