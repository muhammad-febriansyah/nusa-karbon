# 02 — User Goals

## Persona 1 — Admin (Aktor Utama Pengembangan)

**Profil:** Staf marketing atau export manager di perusahaan trader. Terbiasa dengan Excel dan WhatsApp, tidak terbiasa dengan istilah teknis web. Bekerja dari kantor di Indonesia, mayoritas dari laptop.

**Frekuensi pakai:** Harian untuk cek RFQ, mingguan untuk update konten.

### Tujuan

| # | Tujuan | Kenapa penting |
|---|--------|----------------|
| A1 | Melihat inquiry baru begitu masuk tanpa harus membuka email | RFQ yang telat direspons lebih dari 24 jam hampir pasti hilang ke kompetitor |
| A2 | Menandai status tindak lanjut tiap inquiry | Menghindari inquiry yang sama ditangani dua orang atau terlewat sama sekali |
| A3 | Mengekspor daftar inquiry ke CSV | Untuk laporan ke owner dan analisis pasar |
| A4 | Menambah dan mengubah produk beserta spesifikasi teknisnya sendiri | Spesifikasi berubah mengikuti batch produksi; tidak mungkin minta developer tiap kali |
| A5 | Mengunggah dokumen COA, MSDS, SHT per produk | Dokumen diperbarui per batch dan wajib selalu versi terbaru |
| A6 | Mengunggah foto dan video hasil dokumentasi QC | Konten visual adalah aset kepercayaan utama |
| A7 | Mengelola daftar perwakilan resmi | Ada staf keluar-masuk; daftar yang basi justru berbahaya |
| A8 | Menulis dan menjadwalkan artikel blog | SEO jangka panjang bergantung pada konten rutin |
| A9 | Mengisi terjemahan Arab dan Rusia dalam satu form | Bolak-balik antar halaman untuk tiap bahasa memperbesar peluang konten tertinggal kosong |
| A10 | Mengubah data kontak dan alamat perusahaan | Perubahan kantor atau nomor tidak boleh butuh developer |
| A11 | Melihat siapa yang mengubah data sensitif dan kapan | Akuntabilitas, terutama untuk daftar perwakilan |

### Frustrasi yang Harus Dihindari

- Form panjang yang hilang isinya saat gagal validasi
- Upload gambar yang gagal tanpa penjelasan
- Tabel yang tidak bisa dicari atau difilter saat data sudah ratusan baris
- Tidak tahu apakah aksi berhasil atau tidak
- Harus mengisi terjemahan di halaman terpisah untuk tiap bahasa
- Menghapus data secara permanen tanpa peringatan

---

## Persona 2 — Buyer Internasional

**Profil:** Purchasing manager atau pemilik distributor shisha/BBQ di UEA, Arab Saudi, Turki, Rusia, Jerman, atau Korea. Mengevaluasi 5–15 supplier Indonesia sekaligus. Skeptis karena pernah atau sering mendengar kasus penipuan supplier Indonesia.

**Perilaku:** Membuka website dari mobile saat riset awal, dari desktop saat serius. Membandingkan spesifikasi teknis antar supplier dalam spreadsheet. Sangat memperhatikan kelengkapan dokumen legal.

### Tujuan

| # | Tujuan | Kenapa penting |
|---|--------|----------------|
| B1 | Memastikan perusahaan ini nyata dan legal | Ini pertanyaan pertama sebelum yang lain |
| B2 | Melihat spesifikasi teknis lengkap tanpa harus mengisi form dulu | Buyer serius menolak email gate; itu sinyal supplier tidak percaya diri |
| B3 | Mengunduh COA, MSDS, dan SHT | MSDS dan SHT wajib untuk pengurusan impor dan pengangkutan laut |
| B4 | Menghitung muatan kontainer sebelum menghubungi | Menentukan apakah MOQ sesuai kapasitas gudangnya |
| B5 | Memahami siapa yang memproduksi dan siapa yang menjamin kualitas | Buyer berpengalaman tahu perbedaan pabrik dan trader, dan menghargai kejujuran |
| B6 | Memverifikasi bahwa orang yang menghubunginya benar perwakilan resmi | Titik paling rawan penipuan adalah sebelum transfer deposit |
| B7 | Membaca dalam bahasa sendiri | Buyer Arab dan Rusia jauh lebih nyaman dan lebih cepat mengambil keputusan |
| B8 | Mengirim permintaan penawaran dengan detail lengkap | Mempercepat siklus; mengurangi bolak-balik email |
| B9 | Menghubungi lewat WhatsApp | Kanal utama perdagangan di pasar Timur Tengah |
| B10 | Memahami termin pembayaran dan MOQ sebelum negosiasi | Menyaring supplier yang tidak sesuai kapasitasnya |

### Frustrasi yang Harus Dihindari

- Website lambat dibuka dari luar Indonesia
- Terjemahan mesin yang salah pada istilah teknis perdagangan
- Layout Arab yang tidak RTL — terbaca seperti website yang dikerjakan asal
- Klaim "kami pabrik terbesar" tanpa bukti apa pun
- Harus mengisi email untuk melihat spesifikasi dasar
- Nomor kontak yang tidak diangkat atau alamat email gratisan

---

## Persona 3 — Owner / Direktur

**Profil:** Pemilik perusahaan trader. Jarang membuka admin panel, tapi ingin tahu hasilnya.

### Tujuan

| # | Tujuan |
|---|--------|
| O1 | Melihat jumlah inquiry masuk per bulan dan asal negaranya |
| O2 | Memastikan tidak ada inquiry yang terlewat |
| O3 | Yakin bahwa data buyer aman dan tidak bisa diakses sembarang orang |
| O4 | Punya bukti bahwa website ini terlihat kredibel di mata buyer asing |

Karena hanya ada satu role, owner dan staf berbagi akun `admin` atau memiliki akun `admin` masing-masing. Log aktivitas menjadi satu-satunya pembeda jejak — itu sebabnya fitur ini tetap wajib meski role hanya satu.

---

## Pemetaan Tujuan ke Modul

| Tujuan | Modul yang memenuhi |
|--------|---------------------|
| A1, A2, A3, O1, O2 | Dashboard, RFQ Inbox |
| A4, A5, B2, B3 | Manajemen Produk, Spesifikasi, Dokumen Produk |
| A6 | Media Library, Galeri, Upload modern |
| A7, B6, O3 | Perwakilan Resmi, Log Aktivitas |
| A8 | Blog |
| A9, B7 | Form multi-bahasa dengan tab, RTL |
| A10 | Pengaturan Situs |
| A11, O3 | Log Aktivitas |
| B1, B5 | About Us, Sertifikat, Supply Chain Position |
| B4 | Container Calculator |
| B8, B9 | Form RFQ, tombol WhatsApp |
| B10 | Export Info (MOQ, Payment Terms, Shipment Terms) |
