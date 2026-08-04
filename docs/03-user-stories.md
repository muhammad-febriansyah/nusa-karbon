# 03 — User Stories & Acceptance Criteria

Format: `[ID] Sebagai <aktor>, saya ingin <aksi>, agar <manfaat>.`

Prefiks ID:
- `AUTH` — Autentikasi
- `DSH` — Dashboard
- `PRD` — Produk
- `MST` — Master Data
- `CNT` — Konten
- `REP` — Perwakilan Resmi
- `LEAD` — RFQ & Kontak
- `SET` — Pengaturan
- `MED` — Media & Upload
- `PUB` — Website Publik
- `I18N` — Multi-bahasa

---

## AUTH — Autentikasi

### AUTH-01 — Login
Sebagai admin, saya ingin masuk dengan email dan password, agar bisa mengelola konten website.

**Acceptance Criteria**
- Form berisi email, password, dan checkbox "Ingat saya"
- Validasi gagal menampilkan pesan dalam Bahasa Indonesia
- Gagal login 5 kali dalam 1 menit memicu throttle dengan pesan sisa waktu tunggu
- Login berhasil mengarah ke `/admin/dashboard`
- Sesi kedaluwarsa setelah 8 jam tidak aktif
- Halaman login tidak memakai layout admin (tanpa sidebar/breadcrumb)

### AUTH-02 — Lupa & Reset Password
Sebagai admin, saya ingin mereset password lewat email, agar tetap bisa masuk saat lupa.

**Acceptance Criteria**
- Form kirim link reset menerima email terdaftar
- Pesan sukses sama untuk email terdaftar maupun tidak (tidak membocorkan keberadaan akun)
- Link reset berlaku 60 menit dan hanya sekali pakai
- Password baru minimal 12 karakter, wajib kombinasi huruf dan angka
- Setelah reset berhasil, seluruh sesi lain di-logout

### AUTH-03 — Two-Factor Authentication
Sebagai admin, saya ingin mengaktifkan 2FA, agar akun yang memegang data buyer lebih aman.

**Acceptance Criteria**
- 2FA opsional, diaktifkan dari halaman Profil
- Menampilkan QR code dan secret key untuk aplikasi authenticator
- Menyediakan 8 recovery code yang bisa diunduh
- Menonaktifkan 2FA memerlukan konfirmasi password
- Toast Sonner mengonfirmasi setiap perubahan status

### AUTH-04 — Profil & Ganti Password
Sebagai admin, saya ingin mengubah nama, email, foto, dan password saya sendiri.

**Acceptance Criteria**
- Halaman profil terbungkus Card dengan breadcrumb, title, dan description
- Ganti email mewajibkan verifikasi ulang
- Ganti password mewajibkan input password lama
- Foto profil memakai komponen upload gambar dengan crop rasio 1:1

---

## DSH — Dashboard

### DSH-01 — Ringkasan Dashboard
Sebagai admin, saya ingin melihat ringkasan aktivitas begitu masuk, agar tahu apa yang perlu ditindaklanjuti.

**Acceptance Criteria**
- Menampilkan 4 stat card: RFQ baru (belum dibuka), RFQ bulan ini, total produk aktif, total artikel terbit
- Grafik batang inquiry 12 bulan terakhir
- Daftar 10 RFQ terbaru dengan link ke detail
- Peta atau daftar 5 negara asal inquiry terbanyak
- Peringatan bila ada produk aktif yang belum memiliki data master carton (memblokir Container Calculator)
- Peringatan bila ada konten yang belum diterjemahkan ke Arab atau Rusia

---

## PRD — Manajemen Produk

### PRD-01 — Daftar Produk
Sebagai admin, saya ingin melihat daftar produk dalam tabel yang bisa dicari dan difilter.

**Acceptance Criteria**
- DataTable shadcn dengan kolom: gambar, nama (EN), kategori, status, kelengkapan terjemahan, urutan, aksi
- Pencarian global, filter kategori, filter status
- Sorting per kolom, pagination server-side
- Kolom kelengkapan terjemahan menampilkan badge per locale (EN/AR/RU) dengan warna berbeda untuk yang kosong
- Tombol "Tambah Produk" mengarah ke halaman terpisah, **bukan modal**
- Aksi baris: Ubah, Duplikat, Arsipkan
- Bulk action: aktifkan, nonaktifkan, arsipkan

### PRD-02 — Tambah / Ubah Produk
Sebagai admin, saya ingin mengelola produk beserta seluruh detailnya dalam satu halaman.

**Acceptance Criteria**
- Halaman terpisah dengan breadcrumb `Dashboard / Produk / Tambah Produk`
- Form dibagi ke beberapa Card: Informasi Dasar, Konten Multi-bahasa, Data Pengiriman, Media, SEO
- Tab bahasa (EN / AR / RU) untuk field nama, slug, deskripsi singkat, deskripsi lengkap
- Field EN wajib diisi; AR dan RU opsional tapi ditandai bila kosong
- Slug otomatis dari nama, bisa diubah manual, unik per locale
- Kategori memakai searchable select bila opsi lebih dari 10
- Data pengiriman: berat master carton (kg), volume master carton (CBM), tare master carton (kg), berat inner box, jumlah inner box per carton, dimensi carton
- Peringatan inline bila data pengiriman kosong: "Produk ini tidak akan muncul di Container Calculator"
- Upload gambar utama dengan crop rasio 4:3
- Upload galeri multi-file dengan pengurutan drag-and-drop
- Simpan menampilkan toast Sonner sukses dan kembali ke daftar
- Gagal validasi mempertahankan seluruh isi form, termasuk file yang sudah terunggah

### PRD-03 — Spesifikasi Teknis Dinamis
Sebagai admin, saya ingin menambah baris spesifikasi bebas per produk, agar bisa menyesuaikan parameter tiap batch.

**Acceptance Criteria**
- Repeater field di halaman produk: nama parameter, nilai, satuan, urutan, tandai sebagai sorotan
- Nama parameter mendukung tiga bahasa
- Baris bisa ditambah, dihapus, dan diurutkan drag-and-drop
- Tersedia tombol "Isi dari template" yang memuat 9 parameter standar sekaligus
- Minimal 1 baris wajib ada untuk produk berstatus aktif

### PRD-04 — Dokumen Produk
Sebagai admin, saya ingin mengunggah COA, MSDS, dan SHT per produk.

**Acceptance Criteria**
- Tipe dokumen: COA, MSDS, SHT, Packing List, Lainnya
- Hanya menerima PDF dan XLSX, maksimal 10 MB
- Menampilkan nama file, ukuran, tanggal unggah, dan nomor batch opsional
- Mengganti file lama otomatis menghapus file sebelumnya dari storage
- Bisa dinonaktifkan tanpa dihapus
- Unduhan dari sisi publik dicatat jumlahnya

---

## MST — Master Data

### MST-01 — Kategori Produk
Sebagai admin, saya ingin mengelola kategori produk.

**Acceptance Criteria**
- CRUD lengkap dengan nama tiga bahasa, slug, urutan, status
- Soft delete; kategori yang masih dipakai produk tidak bisa dihapus dan menampilkan pesan jelas berisi jumlah produk terkait

### MST-02 — Tipe Kontainer
Sebagai admin, saya ingin mengelola tipe kontainer beserta kapasitasnya, agar Container Calculator selalu akurat.

**Acceptance Criteria**
- Field: kode (20FT_DV), nama tiga bahasa, max payload (kg), max volume (CBM), panjang/lebar/tinggi internal (m), urutan, status
- Validasi: semua nilai numerik harus lebih besar dari nol
- Perubahan langsung memengaruhi hasil kalkulator di sisi publik
- Seeder awal berisi 20FT DV dan 40FT HC

### MST-03 — Sertifikat & Legalitas
Sebagai admin, saya ingin mengelola daftar sertifikat perusahaan.

**Acceptance Criteria**
- Field: nama tiga bahasa, penerbit, nomor sertifikat, logo, file scan, tanggal terbit, tanggal kedaluwarsa, urutan, status
- Sertifikat yang kedaluwarsa dalam 60 hari muncul sebagai peringatan di dashboard
- Perubahan tercatat di log aktivitas

### MST-04 — Opsi Kemasan, Proses Produksi, Protokol QC, Keunggulan, Mitra Pelayaran, Tim
Sebagai admin, saya ingin mengelola konten pendukung ini.

**Acceptance Criteria (berlaku untuk keenamnya)**
- CRUD lengkap dengan judul/nama tiga bahasa, deskripsi tiga bahasa, gambar atau ikon, urutan, status
- Pengurutan drag-and-drop di halaman daftar
- Soft delete
- Protokol QC memiliki field tambahan: label hasil dan nilai target (contoh: "Breakage tolerance" / "< 2.5%")
- Mitra Pelayaran memiliki field tambahan: URL website

---

## CNT — Konten

### CNT-01 — Blog
Sebagai admin, saya ingin menulis dan menerbitkan artikel.

**Acceptance Criteria**
- CRUD artikel dengan judul, slug, ringkasan, konten rich text, gambar sampul, kategori, tag, status
- Status: Draft, Terjadwal, Terbit, Arsip
- Status Terjadwal memakai datepicker dengan waktu; publikasi otomatis lewat scheduler
- Editor rich text mendukung heading, list, tautan, kutipan, tabel, dan penyisipan gambar dari media library
- Field SEO terpisah: meta title, meta description, OG image
- Konten tiga bahasa dengan tab
- Preview artikel sebelum terbit

### CNT-02 — Kategori Artikel
Sebagai admin, saya ingin mengelompokkan artikel.

**Acceptance Criteria**
- CRUD dengan nama tiga bahasa, slug, urutan
- Tidak bisa dihapus bila masih dipakai artikel

### CNT-03 — FAQ
Sebagai admin, saya ingin mengelola daftar pertanyaan yang sering ditanyakan.

**Acceptance Criteria**
- CRUD pertanyaan dan jawaban tiga bahasa
- Dikelompokkan per kategori FAQ (Produk, Pengiriman, Pembayaran, Umum)
- Pengurutan drag-and-drop dalam tiap kategori

### CNT-04 — Galeri
Sebagai admin, saya ingin mengelola album foto dan video.

**Acceptance Criteria**
- Album memiliki judul tiga bahasa, deskripsi, sampul, urutan
- Item galeri berupa gambar atau video (URL YouTube/Vimeo atau file)
- Multi-upload dengan pengurutan drag-and-drop
- Caption per item, tiga bahasa

### CNT-05 — Blok Halaman Statis
Sebagai admin, saya ingin mengubah teks pada halaman About dan Export Info.

**Acceptance Criteria**
- Blok konten terikat pada kunci halaman dan kunci seksi (contoh: `about.company_profile`)
- Tipe blok: teks kaya, teks + gambar, daftar poin
- Kunci blok tidak bisa diubah dari UI (hanya isi kontennya)
- Konten tiga bahasa

---

## REP — Perwakilan Resmi

### REP-01 — Kelola Perwakilan
Sebagai admin, saya ingin mengelola daftar perwakilan resmi perusahaan.

**Acceptance Criteria**
- Field: nama, jabatan tiga bahasa, wilayah, email resmi, nomor telepon/WhatsApp, foto, status aktif, tanggal mulai, tanggal berakhir
- Validasi: email tidak boleh dari domain gratisan (gmail.com, yahoo.com, hotmail.com, outlook.com) — tampilkan peringatan tegas bila dipaksakan
- **Setiap operasi create, update, delete wajib tercatat di log aktivitas** beserta nilai lama dan baru
- Menonaktifkan perwakilan menampilkan dialog konfirmasi yang menjelaskan dampaknya di halaman publik
- Perwakilan nonaktif tidak tampil di sisi publik tapi tetap tersimpan

---

## LEAD — RFQ & Pesan Kontak

### LEAD-01 — Daftar RFQ
Sebagai admin, saya ingin melihat seluruh permintaan penawaran dalam satu tabel.

**Acceptance Criteria**
- DataTable dengan kolom: tanggal, perusahaan, negara, produk, incoterms, tonase, status, aksi
- Filter: status, negara, produk, rentang tanggal (date range picker)
- Pencarian pada nama perusahaan, email, dan port tujuan
- Baris yang belum dibuka ditampilkan tebal dengan penanda
- Badge jumlah RFQ baru di menu sidebar
- Tombol ekspor CSV yang menghormati filter yang sedang aktif

### LEAD-02 — Detail & Tindak Lanjut RFQ
Sebagai admin, saya ingin melihat detail lengkap dan mencatat tindak lanjut.

**Acceptance Criteria**
- Menampilkan seluruh field yang dikirim buyer
- Menampilkan metadata: locale saat submit, negara berdasarkan IP, halaman asal, waktu dalam WIB
- Ubah status: Baru, Dihubungi, Penawaran Dikirim, Negosiasi, Menang, Kalah, Spam
- Tambah catatan tindak lanjut dengan timestamp dan nama admin
- Tombol aksi cepat: buka WhatsApp dengan nomor buyer, salin email, balas email
- Menandai sebagai Spam menyembunyikan dari daftar utama

### LEAD-03 — Notifikasi RFQ Masuk
Sebagai admin, saya ingin diberi tahu saat ada RFQ baru.

**Acceptance Criteria**
- Email notifikasi ke alamat yang diatur di Pengaturan Situs
- Email berisi ringkasan dan tautan langsung ke detail di admin panel
- Opsional: notifikasi WhatsApp bila klien menyediakan gateway
- Notifikasi gagal kirim tidak boleh menggagalkan penyimpanan RFQ

### LEAD-04 — Pesan Kontak Umum
Sebagai admin, saya ingin melihat pesan dari form kontak biasa.

**Acceptance Criteria**
- Daftar dan detail, status dibaca/belum dibaca
- Bisa ditandai sebagai spam
- Terpisah dari RFQ karena bobot bisnisnya berbeda

---

## SET — Pengaturan

### SET-01 — Pengaturan Situs
Sebagai admin, saya ingin mengubah identitas dan kontak perusahaan.

**Acceptance Criteria**
- Dikelompokkan dalam tab: Identitas, Kontak, Sosial Media, SEO, Email, Sistem
- Komponen input dirender otomatis berdasarkan kolom `type` pada tabel setting, sehingga menambah pengaturan baru cukup lewat seeder
- Identitas: nama perusahaan, nama badan hukum, tagline tiga bahasa, logo primary, logo secondary, logo persegi, favicon, apple touch icon, NIB, NPWP, Export ID, HS Code
- Kontak: alamat tiga bahasa, kota, provinsi, kode pos, koordinat peta, URL embed peta, telepon, WhatsApp + pesan awal, email utama, email sales, jam operasional
- Sosial: Facebook, Instagram, LinkedIn, YouTube, TikTok, X, Alibaba — yang kosong tidak dirender di footer
- SEO Default: meta title, meta description, meta keywords, OG image, Google Analytics, GTM, verifikasi Google/Bing/Yandex, Facebook Pixel, toggle robots index
- Sistem: mode maintenance, rate limit RFQ, kunci Turnstile (disimpan terenkripsi)
- Email: alamat penerima notifikasi RFQ (bisa lebih dari satu)
- Perubahan tercatat di log aktivitas

### SET-02 — Log Aktivitas
Sebagai admin, saya ingin melihat riwayat perubahan data penting.

**Acceptance Criteria**
- DataTable dengan kolom: waktu (WIB), pengguna, aksi, objek, alamat IP
- Filter berdasarkan pengguna, tipe objek, dan rentang tanggal
- Detail menampilkan perbandingan nilai lama dan baru
- Log bersifat baca-saja, tidak bisa dihapus dari UI
- Retensi 12 bulan

### SET-04 — Manajemen Pengguna
Sebagai admin, saya ingin mengelola akun admin lain.

**Acceptance Criteria**
- Menu **Manajemen Pengguna** berisi CRUD lengkap **plus halaman detail**
- Daftar: DataTable dengan kolom avatar, nama, email, jabatan, status aktif, login terakhir, aksi
- Aksi baris: **Detail** (amber), **Ubah** (hijau), **Hapus** (merah)
- Halaman detail menampilkan: profil lengkap, status 2FA, waktu & IP login terakhir, jumlah RFQ yang ditangani, dan 20 aktivitas terakhir dari log
- Tambah pengguna mengirim email undangan berisi tautan pembuatan password
- Ubah pengguna tidak menampilkan field password; reset password dilakukan lewat tombol terpisah yang mengirim email
- Upload avatar dengan crop rasio 1:1
- Pengguna tidak dapat menghapus atau menonaktifkan dirinya sendiri — tombolnya dinonaktifkan dengan tooltip penjelas
- Pengguna admin terakhir yang aktif tidak dapat dihapus atau dinonaktifkan
- Hapus dan nonaktifkan memakai dialog konfirmasi yang menyebut nama penggunanya
- Seluruh perubahan tercatat di log aktivitas

### SET-05 — Avatar Pengguna
Sebagai admin, saya ingin mengganti foto profil saya.

**Acceptance Criteria**
- Tersedia di halaman Profil dan di form Manajemen Pengguna
- Memakai `ImageUploader` dengan crop rasio 1:1
- Format jpg, jpeg, png, webp; maksimal 2 MB; dimensi minimal 400 × 400
- Pratinjau lingkaran sebelum disimpan
- Menghapus avatar mengembalikan ke inisial nama di atas latar `primary`
- Avatar tampil di header admin, sidebar, daftar pengguna, dan timeline catatan RFQ
- Mengganti avatar menghapus file lama dari storage

### SET-06 — Tema & Warna
Sebagai admin, saya ingin mengubah palet warna admin panel dan website publik tanpa bantuan developer.

**Acceptance Criteria**
- Menu **Tema & Warna** berisi daftar tema beserta pratinjau palet
- Tersedia 5 preset bawaan yang tidak dapat dihapus maupun diubah
- Tombol Duplikat membuat salinan preset yang bisa diedit
- Editor tema berupa dua kolom: panel pengaturan di kiri, pratinjau langsung yang sticky di kanan
- Setiap field warna punya swatch, input hex, dan keterangan di mana warna itu dipakai
- Pratinjau merender komponen nyata: sidebar, Card, baris tabel dengan tiga tombol aksi, seluruh varian tombol, badge, form field, dan dialog
- Panel pemeriksaan kontras menampilkan rasio setiap pasangan warna
- Kombinasi dengan kontras di bawah 4,5:1 **ditolak saat disimpan**, disertai pesan yang menyebut angkanya
- Tombol "Perbaiki otomatis" mengganti warna teks agar lolos kontras
- Tema dapat diaktifkan terpisah untuk Admin dan untuk Publik
- Aktivasi memakai dialog konfirmasi
- Hanya satu tema aktif per scope pada satu waktu
- Mengganti tema publik mengosongkan cache halaman publik
- Perubahan tema tercatat di log aktivitas
- Tidak ada kedipan warna default saat halaman pertama dimuat

### SET-03 — Media Library
Sebagai admin, saya ingin melihat seluruh file yang pernah diunggah.

**Acceptance Criteria**
- Tampilan grid dengan pratinjau
- Filter berdasarkan tipe (gambar, dokumen, video), rentang tanggal, dan ukuran
- Pencarian berdasarkan nama file
- Detail menampilkan dimensi, ukuran, tanggal unggah, dan daftar entitas yang memakainya
- File yang masih dipakai tidak bisa dihapus; menampilkan daftar penggunanya
- Tersedia penanda file yatim (tidak dipakai entitas mana pun) beserta aksi hapus massal

---

## MED — Media & Upload

### MED-01 — Upload Gambar Modern
Sebagai admin, saya ingin mengunggah gambar dengan cara yang cepat dan jelas.

**Acceptance Criteria**
- Area drag & drop, klik untuk memilih, dan tempel dari clipboard
- Pratinjau tampil sebelum unggah selesai
- Progress bar per file dengan persentase
- Validasi sisi klien: tipe MIME, ukuran maksimal, dimensi minimal — gagal validasi tidak mengirim request
- Crop dan reposisi untuk field yang memiliki rasio wajib
- Membatalkan unggahan yang sedang berjalan
- Gagal unggah menampilkan pesan spesifik penyebabnya, bukan "terjadi kesalahan"
- Konversi otomatis ke WebP beserta varian ukuran responsif
- Menghapus gambar meminta konfirmasi

### MED-02 — Upload Multi-File
Sebagai admin, saya ingin mengunggah banyak gambar sekaligus untuk galeri.

**Acceptance Criteria**
- Pilih atau seret beberapa file sekaligus
- Setiap file punya progress dan status sendiri
- Kegagalan satu file tidak membatalkan yang lain
- Pengurutan drag-and-drop setelah unggah selesai
- Menghapus satu item tanpa memuat ulang halaman

### MED-03 — Upload Dokumen
Sebagai admin, saya ingin mengunggah PDF dokumen resmi.

**Acceptance Criteria**
- Hanya PDF dan XLSX, maksimal 10 MB
- Menampilkan ikon tipe file, nama, dan ukuran
- Pratinjau PDF halaman pertama bila memungkinkan
- Nama file disimpan dengan nama yang dibersihkan, bukan nama asli mentah

---

## PUB — Website Publik

### PUB-01 — Halaman Beranda
Sebagai buyer, saya ingin memahami perusahaan ini dalam 10 detik pertama.

**Acceptance Criteria**
- Hero dengan headline value proposition dan dua CTA
- Video hero memakai poster image sebagai LCP; video tidak autoload di mobile
- Trust badges bar berisi logo sertifikat
- Ringkasan produk, keunggulan, opsi kemasan, mitra pelayaran, artikel terbaru
- LCP di bawah 2 detik pada koneksi 4G

### PUB-02 — Halaman Detail Produk
Sebagai buyer, saya ingin melihat spesifikasi lengkap dan mengunduh dokumen.

**Acceptance Criteria**
- Tabel spesifikasi dari data dinamis
- Tombol unduh COA, MSDS, SHT tanpa form
- Galeri gambar produk
- Opsi kemasan yang tersedia
- CTA ke form RFQ dengan produk terpilih otomatis
- Schema markup `Product`

### PUB-03 — Container Calculator
Sebagai buyer, saya ingin menghitung muatan kontainer.

**Acceptance Criteria**
- Pilih produk dan tipe kontainer
- Menampilkan: jumlah master carton, net weight, gross weight, volume terpakai, persentase utilisasi
- Menampilkan batasan yang mengunci (berat atau volume) secara eksplisit
- Visualisasi SVG kontainer terisi sesuai rasio
- Hanya menampilkan produk yang memiliki data master carton lengkap
- Disclaimer bahwa angka bersifat indikatif
- Berfungsi tanpa JavaScript berat; perhitungan di sisi klien

### PUB-04 — Form RFQ
Sebagai buyer, saya ingin mengirim permintaan penawaran.

**Acceptance Criteria**
- Field sesuai PRD bagian 5.3
- Validasi inline sebelum submit
- Honeypot dan Turnstile aktif
- Rate limit 5 submission per IP per jam
- Halaman terima kasih dengan nomor referensi inquiry
- Email konfirmasi otomatis ke buyer dalam bahasa yang sedang dipakai

### PUB-05 — Verify Our Representative
Sebagai buyer, saya ingin memastikan orang yang menghubungi saya adalah perwakilan resmi.

**Acceptance Criteria**
- Daftar perwakilan aktif beserta foto, jabatan, wilayah, email, dan telepon
- Peringatan eksplisit soal rekening pribadi dan email gratisan
- Pencarian berdasarkan nama
- Tautan dari halaman kontak dan footer

---

## I18N — Multi-Bahasa

### I18N-01 — Ganti Bahasa
Sebagai buyer, saya ingin membaca dalam bahasa saya.

**Acceptance Criteria**
- Switcher di header menampilkan EN, العربية, Русский
- URL berubah ke prefix yang sesuai; halaman yang sama, bukan kembali ke beranda
- Pilihan tersimpan di cookie untuk kunjungan berikutnya
- Bila terjemahan belum ada, tampilkan konten Bahasa Inggris dengan penanda halus, bukan halaman kosong

### I18N-02 — Layout RTL
Sebagai buyer berbahasa Arab, saya ingin layout yang benar.

**Acceptance Criteria**
- `<html dir="rtl" lang="ar">` saat locale Arab
- Seluruh layout ter-mirror: navigasi, sidebar, tabel, ikon arah
- Angka, kode HS, dan istilah Incoterms tetap LTR
- Tidak ada elemen yang terpotong atau tumpang tindih pada 360px

### I18N-03 — Terjemahan di Admin
Sebagai admin, saya ingin mengisi tiga bahasa dalam satu form.

**Acceptance Criteria**
- Tab bahasa di dalam Card, bukan halaman terpisah
- Indikator visual per tab: lengkap, sebagian, kosong
- Berpindah tab tidak menghilangkan isian tab lain
- Bahasa default (EN) wajib; lainnya opsional
- Tersedia tombol "Salin dari Bahasa Inggris" sebagai titik awal penerjemahan
