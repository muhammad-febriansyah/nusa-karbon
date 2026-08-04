# 09 — Task Checklist

Tandai `[x]` hanya bila task sudah selesai **dan** lolos:

```bash
./vendor/bin/pint && ./vendor/bin/phpstan analyse && ./vendor/bin/pest
npm run lint && npx tsc --noEmit
```

Satu task = satu commit. Format pesan commit: `feat(product): tambah CRUD produk` / `fix(upload): perbaiki progress bar multi-file`.

**Legenda estimasi:** S = ≤2 jam · M = ½ hari · L = 1 hari · XL = 2+ hari

---

## FASE 1 — Fondasi

Tanpa fase ini selesai, seluruh fase berikutnya akan menghasilkan kode yang tidak konsisten. Jangan dilewati.

### 1.1 Setup Proyek
- [x] `S` Inisialisasi Laravel 13 + Inertia 3 + React 19 + TypeScript
- [x] `M` Konfigurasi Tailwind v4 dengan token dua lapis `--color-* → var(--t-*, fallback)` sesuai `docs/05-design-system.md` bagian 2
- [x] `S` **Tanpa dark mode** — pastikan tidak ada `darkMode`, kelas `dark:`, atau toggle tema di seluruh proyek
- [x] `S` Setup shadcn/ui, generate komponen dasar (input, card, table, dialog, dropdown-menu, select, tabs, badge, popover, command, sheet, skeleton, label, textarea, switch, checkbox, separator, tooltip, alert)
- [x] `M` **Ganti `buttonVariants` shadcn** dengan peta varian aksi (`create`, `save`, `edit`, `delete`, `detail`, `export`, `upload`, `filter`, `duplicate`, `toggle`, `publish`, `cancel`, `back`, `reset`, `archive`, `whatsapp`). Hapus varian `default`, `ghost`, `outline`, `secondary`, `link`
- [x] `S` Setup Wayfinder + `@laravel/vite-plugin-wayfinder`
- [x] `S` Konfigurasi Pint, Larastan (level 6 minimal), ESLint, Prettier — Larastan level 7
- [x] `S` Konfigurasi Pest 4, buat `TestCase` dasar — terpasang Pest 5
- [ ] `S` Setup `.env.example`, `docker-compose` / Sail, koneksi MySQL `utf8mb4_unicode_ci`
- [ ] `S` Unduh Poppins WOFF2 (300/400/500/600/700), simpan di `public/fonts/poppins/`, muat lewat `@font-face` dengan `font-display: swap`. **Dilarang memakai Google Fonts CDN** — saat ini di-host lokal lewat plugin font Vite di `/build/assets/`, bukan `public/fonts/poppins/`
- [x] `S` Preload Poppins 400 dan 600
- [x] `S` Aktifkan `Model::preventLazyLoading()` di environment non-produksi

### 1.2 Paket
- [x] `S` Install & publish `spatie/laravel-permission`, seed **satu role** `admin`
- [x] `S` Install & publish `spatie/laravel-medialibrary`
- [x] `S` Install & publish `spatie/laravel-activitylog`
- [x] `S` Install `spatie/laravel-translatable`, set `fallback_locale` ke `en`
- [x] `S` Install `mews/purifier` untuk sanitasi rich text
- [x] `S` Install paket npm: TanStack Table, Sonner, react-dropzone, dnd-kit, react-day-picker, date-fns, cmdk, lucide-react, TipTap

### 1.3 Arsitektur
- [x] `M` Buat struktur folder sesuai `docs/06-architecture.md`
- [x] `S` Buat `BaseRepository` dan `RepositoryServiceProvider` untuk binding interface
- [x] `S` Buat seluruh Enum: `RfqStatus`, `PostStatus`, `DocumentType`, `Incoterm`, `PageBlockType`, `GalleryItemType`
- [x] `S` Buat `Support/Locale.php` dan `Support/Rupiah.php`
- [x] `S` Buat `BlameableObserver` yang mengisi `created_by` dan `updated_by` otomatis
- [ ] `S` Konfigurasi timezone: DB UTC, tampilan `Asia/Jakarta` — **dibatalkan atas permintaan klien**, `config/app.php` tetap `Asia/Jakarta`, jadi timestamp tersimpan waktu Jakarta dan tidak perlu konversi di Resource

### 1.4 Autentikasi
- [x] `M` Setup Fortify: login, logout, reset password, konfirmasi password — passkey dinonaktifkan sementara atas permintaan klien, komponennya disimpan di `resources/js/disabled/passkeys/`
- [x] `M` Halaman login dengan `AuthLayout` (tanpa sidebar), validasi Bahasa Indonesia
- [x] `M` Halaman lupa password & reset password
- [x] `M` 2FA: aktivasi, QR code, recovery codes, nonaktifkan
- [x] `S` Throttle login 5 percobaan per menit dengan pesan sisa waktu
- [x] `S` Middleware `EnsureUserIsActive`
- [x] `S` Catat `last_login_at` dan `last_login_ip`
- [x] `M` Halaman profil: ubah nama, email, foto, password — kolom `position` dibatalkan atas permintaan klien
- [x] `S` Test: guest tidak bisa akses rute admin, throttle bekerja, reset token sekali pakai

### 1.5 Layout Admin
- [x] `L` `AdminLayout`: sidebar + header + area konten + Sonner Toaster
- [x] `M` Sidebar dengan grup menu sesuai `docs/07-admin-ui-standards.md` bagian 7
- [x] `S` State ciut sidebar disimpan di cookie
- [x] `S` Sidebar jadi Sheet di bawah 1024px
- [x] `S` Badge jumlah RFQ & pesan belum dibaca lewat shared props Inertia — penghitungnya masih `0` sampai model `RfqRequest` dan `ContactMessage` dibuat di Fase 4
- [x] `S` Progress bar navigasi Inertia
- [x] `S` Hook `useFlashToast` dipasang di layout — dipasang di `withApp()` pada `app.tsx` lewat komponen `Toaster`, jadi berlaku untuk halaman auth juga

### 1.6 Komponen Bersama — Wajib Selesai Sebelum Fase 2
- [x] `M` `PageHeader` (breadcrumb + title + description + slot aksi)
- [x] `L` `DataTable` generik: pagination server-side, pencarian debounce, sorting, pemilih kolom, pilihan per-page, bulk select, skeleton, empty state, sinkronisasi query string
- [x] `S` `DataTableColumnHeader`, `DataTablePagination`, `DataTableToolbar`, `DataTableRowActions`
- [x] `S` `EmptyState` (dua varian: data kosong vs hasil filter kosong)
- [x] `S` `EditButton`, `DetailButton`, `DeleteButton` — komponen aksi terikat varian + ikon
- [x] `M` `ConfirmDialog` — tone `danger`/`warning`/`info`/`success`, ikon berlingkaran, judul menyebut nama objek, tombol kata kerja spesifik, state loading yang mengunci dialog
- [x] `S` `InfoDialog` — untuk kasus "tidak dapat dihapus karena masih dipakai"
- [x] `S` `BulkConfirmDialog` — menyebut jumlah dan 5 nama pertama
- [x] `S` Lint rule / review checklist: tolak `variant="ghost"`, `variant="outline"`, dan `window.confirm()` — `no-restricted-syntax` di `eslint.config.js`, juga menolak `secondary`/`link`/`default` serta `window.alert`/`window.prompt`
- [x] `S` `StatusBadge` berbasis Enum
- [x] `S` `TranslationIndicator` (titik EN/AR/RU)
- [x] `M` `FormFieldWrapper` (label + input + helper + error + `aria-describedby`)
- [x] `S` `InputRupiah` — pemisah ribuan, kirim integer murni
- [x] `S` `InputDecimal` — untuk berat & CBM, koma di tampilan, titik saat dikirim
- [x] `M` `DatePicker` — react-day-picker, locale `id`, format `dd MMMM yyyy`
- [x] `M` `DateRangePicker` — dengan preset rentang
- [x] `L` `ComboboxSelect` — searchable (pola select2), keyboard nav, mode multiple, clearable, empty state, loading state
- [ ] `S` Virtualisasi pada `ComboboxSelect` untuk opsi > 100 — belum; `CommandList` sudah bergulir, virtualisasi ditunda sampai ada daftar nyata (pemilih negara)
- [x] `M` `TranslatableTabs` — tab EN/AR/RU, indikator kelengkapan, tombol salin dari EN, `dir="rtl"` pada tab Arab, penanda error di tab tidak aktif
- [x] `M` `SortableList` — drag-and-drop dnd-kit, kirim urutan baru ke server
- [x] `L` `RichTextEditor` — TipTap: heading, list, link, blockquote, sisip gambar dari media library — ekstensi tabel belum dipasang

### 1.7 Sistem Upload — Prioritas Tinggi
- [x] `L` `ImageUploader`: drag & drop, klik, tempel clipboard, pratinjau instan, progress, batalkan, validasi klien (tipe/ukuran/dimensi), pesan error spesifik
- [x] `M` Crop & reposisi untuk field berasio wajib — `AvatarUploader` (crop 1:1, geser & perbesar)
- [x] `L` `MultiImageUploader`: multi-file, progress per file, kegagalan satu file tidak membatalkan lainnya, urut ulang drag-and-drop, hapus per item
- [ ] `M` `DocumentUploader`: PDF & XLSX, ikon tipe, nama, ukuran, pratinjau halaman pertama PDF — komponen selesai kecuali **pratinjau halaman pertama PDF** (butuh pdf.js, belum ada di daftar paket)
- [ ] `M` `MediaUploadService` sisi server: sanitasi nama file, konversi WebP, varian thumb/medium/large, hapus file lama saat diganti — sanitasi nama & hapus file lama selesai; **konversi WebP dan varian thumb/medium/large belum**, menunggu model pertama yang memakai medialibrary di Fase 2
- [x] `S` Sanitasi SVG untuk upload logo
- [x] `S` Test: validasi ukuran & tipe, file lama terhapus saat diganti

---

## FASE 2 — Admin Panel: Master Data

> **Blocker:** minta data teknis master carton dari klien sebelum memulai `2.2`. Container Calculator tidak bisa diselesaikan tanpa itu.

### 2.1 Kategori Produk
- [x] `S` Migration, Model (translatable + softDeletes), Factory, Seeder
- [x] `S` Repository + Interface + binding
- [x] `S` Action: Create, Update, Delete
- [x] `S` FormRequest dengan pesan Bahasa Indonesia
- [x] `M` Halaman index (DataTable + drag urut) dan create/edit — index memakai DataTable dengan kolom urutan yang bisa disortir; **drag-and-drop di layar belum**, endpoint `reorder` dan testnya sudah ada. Create/edit berupa **dialog**, bukan halaman terpisah
- [x] `S` Cegah hapus bila masih dipakai produk, tampilkan jumlahnya — `CategoryInUseException` memuat jumlah produknya
- [x] `S` Test CRUD + proteksi hapus

### 2.2 Produk
- [x] `M` Migration `products` lengkap dengan field master carton
- [x] `S` Model + relasi (category, specifications, documents, packagingOptions) + scope `calculable()` — ditambah relasi `rfqRequests()` untuk halaman detail
- [x] `S` Factory + Seeder (3 produk contoh dengan data carton lengkap) — seeder berisi 6 produk; angkanya masih **data sementara** menunggu klien
- [x] `M` Repository: paginate dengan filter, findBySlug per locale, calculable
- [x] `M` Action: Create, Update, Delete, Duplicate
- [x] `M` FormRequest store & update
- [x] `L` Halaman index: DataTable dengan kolom gambar, nama, kategori, status, kelengkapan terjemahan, urutan, aksi
- [x] `S` Filter kategori & status, bulk action aktif/nonaktif/arsip
- [x] `XL` Halaman create/edit: multi-Card, tab bahasa, upload sampul + galeri, repeater spesifikasi — **tab bahasa belum**; form masih satu locale (`id`), menyusul bersama Fase 6
- [x] `S` Peringatan inline bila data master carton kosong — ikon peringatan di daftar dan spanduk di halaman detail
- [x] `S` Slug otomatis dari nama, bisa diubah, validasi unik per locale
- [x] `M` Test: CRUD, slug unik per locale, scope calculable
- [x] `M` Halaman detail produk: spesifikasi, dokumen, estimasi muatan kontainer, RFQ terkait — **tambahan di luar rencana awal**, dipakai tombol Detail di daftar produk

### 2.3 Spesifikasi Produk
- [x] `S` Migration + Model + Factory
- [x] `M` Repeater di form produk: tambah, hapus, urut drag-and-drop
- [x] `S` Tombol "Isi dari Template" (9 parameter standar)
- [x] `S` `SyncProductSpecificationsAction`
- [x] `S` Test: sinkronisasi tidak menduplikasi baris

### 2.4 Dokumen Produk
- [x] `S` Migration + Model + Factory
- [x] `M` Bagian dokumen di form produk memakai `DocumentUploader`
- [x] `S` Tipe: COA, MSDS, SHT, Packing List, Lainnya
- [x] `S` Field batch reference dan tanggal terbit
- [x] `S` Ganti file menghapus file lama
- [x] `S` Migration + Model `document_downloads`
- [x] `S` Test: hanya PDF/XLSX diterima, maksimal 10 MB

### 2.5 Tipe Kontainer
- [x] `S` Migration + Model + Factory + Seeder (20FT DV, 40FT HC)
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index & create/edit
- [x] `S` Validasi seluruh nilai numerik harus > 0
- [x] `S` Test CRUD + validasi

### 2.6 Opsi Kemasan
- [x] `S` Migration + pivot `product_packaging_option` + Model + Factory + Seeder
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index (drag urut) & create/edit dengan upload gambar
- [x] `S` Multi-select kemasan di form produk
- [x] `S` Test CRUD + sinkronisasi pivot

### 2.7 Container Calculator Service
- [x] `M` `ContainerCalculatorService` sesuai `docs/06-architecture.md` bagian 5.5
- [x] `S` Duplikasi logika di TypeScript (`lib/container-calculator.ts`)
- [x] `M` Test unit: weight-limited, volume-limited, data tidak lengkap, batas atas, pembulatan
- [x] `S` Pastikan hasil PHP dan TypeScript identik untuk seluruh kombinasi seeder

> Dikerjakan memakai **data master carton sementara** di `ProductSeeder` karena data asli klien belum turun. Rumus dan test sudah final; begitu data resmi diterima cukup ganti angka di seeder — tidak ada perubahan logika.

---

## FASE 3 — Admin Panel: Konten

### 3.1 Sertifikat
- [x] `S` Migration + Model + Factory + Seeder
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index & create/edit: upload logo + file scan, tanggal terbit & kedaluwarsa
- [x] `S` Toggle `show_in_trust_bar`
- [x] `S` Aktifkan `LogsActivity`
- [x] `S` Peringatan dashboard untuk sertifikat kedaluwarsa dalam 60 hari
- [x] `S` Test CRUD + audit log

### 3.2 Perwakilan Resmi
- [x] `S` Migration + Model + Factory + Seeder
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index & create/edit dengan upload foto
- [x] `S` Validasi tolak domain email gratisan dengan peringatan tegas — aturan `App\Rules\CompanyEmail`
- [x] `S` `LogsActivity` dengan `logAll()` — mencatat nilai lama & baru
- [x] `S` Dialog konfirmasi nonaktifkan yang menjelaskan dampak di halaman publik
- [x] `M` Test: setiap create/update/delete tercatat di activity log

### 3.3 Konten Perusahaan Berulang
Pola identik untuk keenamnya: Migration, Model, Factory, Seeder, Repository, Action, FormRequest, halaman index dengan drag urut, halaman create/edit dengan tab bahasa + upload.

- [x] `M` Proses Produksi (`production_steps`)
- [x] `M` Protokol QC (`qc_protocols`) — tambahan field `result_label` & `result_value`
- [x] `M` Keunggulan (`features`) — field ikon lucide tervalidasi; pemilih visual menyusul bersama Media Library 4.4
- [x] `M` Mitra Pelayaran (`shipping_partners`) — tambahan field URL
- [x] `M` Tim (`team_members`) — upload foto rasio 1:1
- [x] `S` Test CRUD untuk keenamnya

> Kelimanya berbagi `SortableContentRepository`, `App\Actions\Content\*`, serta komponen `SortableContentIndex` dan `ContentForm` — perilakunya identik, jadi hanya konfigurasi field yang berbeda per modul.

### 3.4 Blog
- [x] `S` Migration `post_categories`, `posts`, `tags`, `post_tag`
- [x] `S` Generated column `title_en` + index untuk pencarian — kolom `search_title` mengikuti locale utama; ekspresinya menyesuaikan MySQL dan SQLite
- [x] `S` Model + relasi + scope published + Factory + Seeder
- [x] `S` Repository + Action (termasuk `PublishPostAction`)
- [x] `M` FormRequest + sanitasi HTML lewat Purifier
- [x] `L` Halaman index: DataTable dengan filter status, kategori, rentang tanggal
- [x] `XL` Halaman create/edit: TipTap, tab bahasa, upload sampul, kategori (combobox), tag, field SEO
- [x] `M` Status terjadwal + command scheduler untuk publikasi otomatis
- [x] `S` Hitung `reading_minutes` otomatis
- [x] `M` Halaman preview sebelum terbit
- [x] `M` CRUD kategori artikel
- [x] `S` Test: penjadwalan, sanitasi HTML, slug unik per locale

### 3.5 FAQ
- [x] `S` Migration `faq_categories` & `faqs` + Model + Factory + Seeder
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index dikelompokkan per kategori dengan drag urut dalam kategori
- [x] `M` Halaman create/edit dengan tab bahasa — form sudah siap multi-locale; komponen `TranslatableTabs` dipasang bersama Fase 6
- [x] `S` Test CRUD

### 3.6 Galeri
- [x] `S` Migration `galleries` & `gallery_items` + Model + Factory
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman index album
- [x] `L` Halaman kelola item: `MultiImageUploader`, dukungan video (URL/file), caption tiga bahasa, urut drag-and-drop
- [x] `S` Test CRUD + urutan

### 3.7 Blok Halaman Statis
- [x] `S` Migration `page_blocks` + Model + Seeder seluruh kunci blok
- [x] `S` Repository + Action + FormRequest
- [x] `M` Halaman kelola dikelompokkan per `page_key`, hanya isi yang bisa diubah
- [x] `S` Editor sesuai `type` blok — TipTap untuk `rich_text`, repeater butir untuk `list`, nilai & satuan untuk `stat`, upload gambar untuk `text_image`
- [x] `S` Test: kunci blok tidak bisa diubah dari UI

---

## FASE 4 — Admin Panel: Leads, Sistem, Dashboard

### 4.1 RFQ Inbox
- [x] `S` Migration `rfq_requests` & `rfq_notes` + Model + Factory — seeder RFQ sengaja tidak dibuat; datanya datang dari form publik
- [x] `S` Generator `reference_no` format `RFQ-YYYYMMDD-NNNN`
- [x] `M` Repository dengan filter status, negara, produk, rentang tanggal
- [x] `M` Action: UpdateStatus, AddNote, MarkAsSpam, MarkAsRead
- [x] `L` Halaman index: DataTable, baris belum dibaca ditebalkan, badge status
- [x] `S` Filter rentang tanggal memakai `DateRangePicker`
- [x] `M` Halaman detail: seluruh field, metadata (locale, negara, waktu WIB), timeline catatan
- [x] `S` Aksi cepat: buka WhatsApp, salin email, ubah status
- [x] `M` Ekspor CSV yang menghormati filter aktif
- [x] `M` Notifikasi email RFQ baru (queued, kegagalan tidak menggagalkan simpan) — dipakai `CreateRfqRequestAction`, dipanggil form publik pada Fase 5.3
- [x] `M` Test: filter, ekspor, penomoran referensi — test notifikasi menyusul bersama form publik Fase 5.3

### 4.2 Pesan Kontak
- [x] `S` Migration + Model + Factory
- [x] `M` Halaman index & detail, tandai dibaca/spam
- [x] `S` Test CRUD

### 4.3 Pengaturan Situs
- [x] `S` Migration `site_settings` dengan kolom `label`, `description`, `type`, `rules`, `sort_order`, `is_public`
- [x] `M` Seeder seluruh kunci: grup `identity` (11), `contact` (14), `social` (7), `seo` (12), `email` (5), `system` (7)
- [x] `M` `SettingService` dengan cache permanen + invalidasi saat berubah
- [x] `S` Enkripsi otomatis untuk kunci rahasia (`turnstile_secret_key`)
- [x] `L` Halaman pengaturan dengan tab per grup, komponen input dirender otomatis berdasarkan kolom `type`
- [x] `S` Renderer untuk tipe `image`, `file`, `email_list`, `translatable`, `color`, `coordinate`, `boolean`, `number`
- [x] `S` Upload logo dan favicon — varian secondary/square/apple-touch dibatalkan klien; OG image tetap ada di grup SEO
- [ ] `S` Field peta: latitude, longitude, dan pratinjau embed — field koordinat & URL embed sudah ada; **pratinjau peta belum** dirender
- [x] `S` Validasi `email_list` — multi-email dipisah koma
- [x] `S` `LogsActivity` pada perubahan pengaturan
- [x] `S` Kirim hanya setting `is_public = true` ke frontend publik — `SettingService::publicSettings()`, dipasang ke shared props saat Fase 5.1
- [x] `M` Test: cache ter-invalidasi, kunci rahasia tidak bocor ke frontend

### 4.3B Tema & Warna
- [x] `S` Migration `themes` + Model + Factory
- [x] `M` Seeder 5 preset: `ember-light`, `charcoal-dark`, `slate-neutral`, `forest-green`, `navy-corporate` — seluruh preset lolos ambang kontras yang sama dengan yang divalidasi server
- [x] `M` `ThemeService`: `tokensFor()`, `toCssVariables()`, `flush()`, dengan cache
- [x] `M` Injeksi `<style id="theme-tokens">` di Blade root — **server-side**, bukan `useEffect`
- [x] `M` `Support/Color.php`: `relativeLuminance()`, `contrastRatio()`, `bestForeground()`
- [x] `M` `StoreThemeRequest` / `UpdateThemeRequest`: hanya hex 6 digit, kunci tetap, seluruh token wajib
- [x] `M` Validasi kontras — tolak simpan bila pasangan `*_foreground` di bawah 4,5:1
- [x] `S` Aksi "Perbaiki otomatis" memakai `bestForeground()`
- [x] `M` Halaman daftar tema: kartu pratinjau nyata per tema + penanda aktif per scope — bukan DataTable; admin non-teknis memilih dari tampilan, bukan dari nama palet
- [x] `XL` Editor tema dua kolom: panel warna kiri, pratinjau langsung sticky di kanan
- [x] `L` Komponen pratinjau: sidebar, Card, baris aksi 3 tombol, varian tombol, badge, form field — dialog mini belum ikut dirender
- [x] `S` Debounce pratinjau 150 ms, `applyPreviewTokens` + pemulihan saat meninggalkan halaman
- [x] `M` `ActivateThemeAction` dalam transaksi, satu aktif per scope, flush cache setelah commit
- [x] `S` Aktivasi tema memakai dialog konfirmasi
- [x] `S` Preset tidak dapat dihapus maupun diubah; tombolnya dinonaktifkan dengan tooltip
- [x] `S` Aksi Duplikat untuk membuat salinan preset yang bisa diedit
- [ ] `S` Mengganti tema publik mengosongkan cache halaman publik — cache token sudah dikosongkan; cache halaman publik menyusul bersama Fase 5.1
- [x] `S` `LogsActivity` pada perubahan dan aktivasi tema
- [x] `M` Test: kontras ditolak, satu aktif per scope, preset terlindungi, hex tidak valid ditolak, CSS dirender server

### 4.4 Media Library

> Pemakaian berkas dilacak lewat peta kolom di `MediaLibraryService::PATH_COLUMNS`. Modul baru yang menyimpan path **wajib** ditambahkan ke peta itu — kalau tidak, berkasnya terbaca yatim dan bisa ikut terhapus saat pembersihan massal.
- [x] `M` Halaman grid dengan pratinjau, filter tipe & tanggal, pencarian nama file
- [x] `M` Panel detail: dimensi, ukuran, tanggal, daftar entitas yang memakai
- [x] `S` Cegah hapus file yang masih dipakai, tampilkan daftar penggunanya
- [x] `M` Deteksi file yatim + aksi hapus massal
- [x] `S` Dialog pemilih media untuk disisipkan ke TipTap — dipasang di editor artikel dan blok halaman
- [x] `S` Test: proteksi hapus file terpakai

### 4.5 Log Aktivitas
- [x] `M` Halaman index: DataTable dengan filter pengguna, tipe objek, rentang tanggal
- [x] `M` Panel detail: perbandingan nilai lama vs baru
- [x] `S` Baca-saja, tidak bisa dihapus dari UI
- [x] `S` Command pembersihan log lebih dari 12 bulan — `activity-log:prune`, dijadwalkan bulanan
- [x] `S` Test: log tercatat untuk model sensitif

### 4.6 Manajemen Pengguna
- [x] `S` Migration tambahan pada `users`: `avatar_path`, `phone` — kolom `position` dibatalkan klien
- [x] `S` Repository + Action: Create, Update, Delete, ToggleActive — undangan dan reset password memakai satu alur tautan sekali pakai
- [x] `S` FormRequest store & update (update tidak memuat field password)
- [x] `M` Halaman index: DataTable dengan avatar, nama, email, status, login terakhir
- [x] `S` Aksi baris: `DetailButton` (amber), `EditButton` (hijau), `DeleteButton` (merah)
- [x] `M` Halaman **detail pengguna**: profil, status 2FA, login terakhir + IP, jumlah RFQ ditangani, 20 aktivitas terakhir dari log
- [x] `M` Halaman create/edit dengan upload avatar (crop 1:1)
- [x] `M` Email undangan berisi tautan pembuatan password untuk pengguna baru
- [x] `S` Tombol "Kirim ulang undangan" dan "Kirim tautan reset password" — satu tombol, alurnya identik
- [x] `S` Cegah pengguna menonaktifkan atau menghapus dirinya sendiri — tombol dinonaktifkan dengan tooltip penjelas
- [x] `S` Cegah penghapusan atau penonaktifan pengguna admin aktif terakhir
- [x] `S` Dialog konfirmasi menyebut nama pengguna
- [x] `S` `LogsActivity` pada seluruh perubahan pengguna
- [x] `M` Test: proteksi diri sendiri, proteksi admin terakhir, undangan terkirim, avatar lama terhapus saat diganti

### 4.6B Avatar
- [x] `S` `AvatarUploader` berbasis `ImageUploader` dengan crop 1:1, pratinjau lingkaran
- [x] `S` Fallback inisial nama di atas latar `primary` bila avatar kosong
- [x] `S` Avatar tampil di header admin, sidebar, daftar pengguna, timeline catatan RFQ — **kecuali timeline catatan RFQ**; di sana baru nama penulisnya yang tampil
- [x] `S` Validasi: jpg/jpeg/png/webp, maksimal 2 MB, minimal 400 × 400
- [x] `S` Mengganti avatar menghapus file lama dari storage
- [x] `S` Test: file lama terhapus, fallback inisial benar

### 4.7 Dashboard
- [x] `M` 4 stat card: RFQ belum dibaca, RFQ bulan ini, produk aktif, artikel terbit
- [x] `M` Grafik batang inquiry 12 bulan terakhir
- [x] `S` Daftar 10 RFQ terbaru
- [x] `S` 5 negara asal inquiry terbanyak
- [x] `M` Panel peringatan: produk tanpa data master carton, sertifikat akan kedaluwarsa, konten belum diterjemahkan
- [x] `S` Cache query dashboard 5 menit
- [x] `S` Test: statistik akurat
- [x] `M` Grafik memakai ApexCharts dengan warna dari token tema — batang 12 bulan, radial tingkat konversi, corong status, batang peringkat negara & produk
- [x] `S` Tingkat konversi (spam dikecualikan), corong status RFQ, produk paling diminati, umpan aktivitas, ringkasan isi situs

### 4.8 Kerangka Admin — Pencarian & Notifikasi
- [x] `M` Pencarian global lintas modul (produk, RFQ, artikel, FAQ, sertifikat, perwakilan, pesan kontak) dengan debounce 300 ms dan pembatalan permintaan lama
- [x] `S` Dialog perintah dengan pintasan ⌘K / Ctrl+K, hasil dikelompokkan per modul, maksimal 5 per kelompok
- [x] `S` Lonceng notifikasi: RFQ dan pesan kontak belum dibaca, dimuat saat dropdown dibuka
- [x] `S` Logo, favicon, dan nama situs diambil dari `site_settings`, bukan berkas statis
- [x] `S` Test: pencarian per modul, batas panjang kata kunci, isi notifikasi

### 4.9 Data Demo
- [x] `L` `DemoSeeder`: 30 baris per modul beserta foto dan berkasnya, dijalankan manual dan berhenti sendiri di produksi
- [x] `M` `DemoAssetFetcher`: foto berlisensi komersial lewat Openverse, potret ilustrasi DiceBear, logo pelayaran public domain dari Wikimedia Commons
- [x] `S` `DemoPdf`: penulis PDF 1.4 untuk COA, MSDS, Packing List, dan berkas sertifikat
- [x] `S` Test: jumlah baris, kolom berkas tidak kosong, penjaga produksi, PDF sah

---

## FASE 5 — Website Publik

### 5.1 Fondasi Publik
- [x] `L` `PublicLayout`: header, nav, language switcher, footer — navbar memakai port `resizable-navbar` Aceternity di `components/public/`; menyusut 80rem ke 68rem saat digulir, bukan ke 40% seperti aslinya. Language switcher tidak dirender selama `config/locales.php` baru punya satu bahasa, dan muncul sendiri di Fase 6
- [x] `M` Komponen `<SeoHead>`: title, meta, OG, canonical, hreflang — `hreflang` baru terbit saat locale lebih dari satu
- [x] `M` Schema markup: Organization, BreadcrumbList
- [x] `S` Aktifkan Inertia SSR — `npm run build` kini ikut membangun bundel SSR; test dijalankan dengan `INERTIA_SSR_ENABLED=false`
- [x] `S` Terapkan tema publik aktif lewat `ThemeService` (bukan warna hardcoded) — sekalian memperbaiki `ThemeService` yang menyimpan hasil kosong ke cache selama 30 hari bila request datang sebelum seeder dijalankan
- [ ] `S` Muat font Arab hanya saat locale `ar` — menyusul bersama Fase 6; locale `ar` belum aktif di `config/locales.php`

> Halaman publik yang isinya belum dikerjakan sudah punya rute final di `routes/public.php` dan dilayani `ComingSoonController`. Menetapkan URL lebih awal menjaga navigasi, footer, dan Wayfinder tidak menunjuk tautan mati, dan mengisi halamannya nanti tidak mengubah satu pun alamat.

### 5.2 Halaman
- [x] `XL` Beranda: hero, trust badges, produk, keunggulan, kemasan, mitra pelayaran, artikel terbaru, CTA — setiap bagian menghilangkan dirinya sendiri saat datanya kosong. Naskah hero, intro, dan CTA dibaca dari `page_blocks`, tidak ada teks pemasaran yang ditanam di kode
- [x] `M` Hero: poster image sebagai LCP, video tidak autoload di mobile — video baru diunduh setelah tombol putar ditekan, di semua ukuran layar, bukan hanya mobile
- [ ] `L` About Us: profil, tim, posisi rantai pasok, sertifikat, galeri
- [ ] `M` Products index
- [ ] `L` Product detail: tabel spesifikasi, unduh dokumen, galeri, kemasan, CTA RFQ, schema `Product`
- [ ] `L` Export Info: proses produksi, MOQ, termin bayar, termin kirim, kemasan, FAQ
- [ ] `XL` **Container Calculator**: pemilih produk & kontainer, visualisasi SVG, readout, label batasan, disclaimer
- [ ] `M` Blog index dengan pagination & filter kategori
- [ ] `M` Blog detail + schema `Article` + artikel terkait
- [ ] `L` Contact: form RFQ, form kontak, peta, info kontak
- [ ] `M` **Verify Our Representative**: daftar, pencarian, peringatan penipuan
- [ ] `S` Halaman 404 dan 500 yang sesuai brand

### 5.3 Form Publik
- [ ] `M` Form RFQ dengan seluruh field, validasi inline
- [ ] `S` Honeypot + Cloudflare Turnstile
- [ ] `S` Rate limit 5 submission per IP per jam
- [ ] `S` Halaman terima kasih dengan nomor referensi
- [ ] `M` Email konfirmasi ke buyer dalam locale yang dipakai
- [ ] `S` Form kontak umum
- [ ] `M` Test: validasi, rate limit, honeypot, email terkirim

### 5.4 Unduhan
- [ ] `M` Endpoint unduh dokumen + pencatatan `document_downloads`
- [ ] `S` Signed URL untuk dokumen
- [ ] `S` Test pencatatan unduhan

---

## FASE 6 — Multi-Bahasa & RTL

- [ ] `M` Middleware `SetLocale` + routing prefix
- [ ] `S` File bahasa `en`, `ar`, `ru` untuk teks UI publik
- [ ] `M` Language switcher yang mempertahankan halaman saat ini
- [ ] `S` Cookie preferensi bahasa
- [ ] `L` **Audit RTL**: ganti seluruh kelas arah fisik ke logical properties
- [ ] `M` `dir` dan `lang` benar sejak SSR, bukan lewat `useEffect`
- [ ] `M` Bungkus angka, kode, dan istilah Incoterms dengan `dir="ltr"`
- [ ] `S` Toast Sonner pindah ke `top-left` saat RTL
- [ ] `S` Ikon arah `rtl:rotate-180`
- [ ] `M` Font Arab (Poppins Arabic atau pengganti) di-host lokal, dimuat hanya saat locale `ar`
- [ ] `M` Hreflang lengkap + `x-default` di seluruh halaman
- [ ] `M` Sitemap terpisah per locale + sitemap index
- [ ] `S` Halaman belum diterjemahkan dikecualikan dari sitemap locale bersangkutan
- [ ] `M` Test: seluruh halaman dirender di 3 locale, RTL diperiksa di 360/768/1440px
- [ ] `S` Aturan lint yang menolak kelas arah fisik

---

## FASE 7 — SEO, Performa, Hardening

### 7.1 SEO
- [ ] `S` `robots.txt` dinamis per environment
- [ ] `M` Schema `Product`, `Article`, `FAQPage`, `Organization`
- [ ] `S` Canonical di seluruh halaman
- [ ] `S` OG image fallback bila konten tidak punya gambar

### 7.2 Performa
- [ ] `M` Audit LCP di Home, Product Detail, Blog Detail
- [ ] `S` Preload hero image, `fetchpriority="high"`
- [ ] `S` `loading="lazy"` untuk gambar di luar viewport
- [ ] `S` Konversi WebP/AVIF + `srcset` responsif
- [ ] `M` Code splitting per rute, analisis ukuran bundel
- [ ] `S` Cache response halaman publik
- [ ] `S` Konfigurasi Cloudflare CDN + cache rules
- [ ] `M` Verifikasi PageSpeed > 90 mobile, > 95 desktop
- [ ] `S` Cek tidak ada N+1 query (aktifkan `preventLazyLoading` saat audit)

### 7.3 Keamanan
- [ ] `S` Header keamanan: CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy
- [ ] `S` Rate limit seluruh endpoint publik
- [ ] `S` Sanitasi seluruh output rich text
- [ ] `S` Verifikasi upload: whitelist MIME, cek ekstensi ganda, simpan di luar public root
- [ ] `S` Password policy minimal 12 karakter
- [ ] `S` Audit: pastikan `.env`, storage, dan log tidak dapat diakses publik
- [ ] `M` Uji manual: percobaan XSS pada rich text, upload file berbahaya, path traversal

### 7.4 Operasional
- [ ] `M` Backup otomatis database harian (retensi 30 hari) & media mingguan
- [ ] `S` Konfigurasi queue worker + supervisor
- [ ] `S` Konfigurasi scheduler (publikasi terjadwal, pembersihan log)
- [ ] `S` Log error terpantau
- [ ] `M` Dokumentasi deployment
- [ ] `L` Panduan penggunaan admin panel untuk klien (Bahasa Indonesia, dengan tangkapan layar)

---

## Ringkasan Estimasi

| Fase | Estimasi |
|------|----------|
| Fase 1 — Fondasi | 9–11 hari |
| Fase 2 — Master Data | 6–8 hari |
| Fase 3 — Konten | 8–10 hari |
| Fase 4 — Leads, Tema & Sistem | 9–11 hari |
| Fase 5 — Website Publik | 10–12 hari |
| Fase 6 — Multi-bahasa & RTL | 5–7 hari |
| Fase 7 — SEO & Hardening | 4–5 hari |
| **Total** | **51–64 hari kerja** |

Belum termasuk: revisi desain, pengisian konten, terjemahan, dan waktu tunggu data dari klien.

Fase 1 terlihat lama untuk "belum menghasilkan fitur apa pun", tapi seluruh komponen di 1.6 dan 1.7 akan dipakai berulang di 40+ halaman. Memotong fase ini berarti membayarnya kembali dengan bunga di fase-fase berikutnya.

Fase 4 bertambah sekitar 3 hari dibanding versi sebelumnya karena tambahan sistem tema (4.3B), pengaturan situs yang jauh lebih lengkap (4.3), serta manajemen pengguna dengan halaman detail dan avatar (4.6, 4.6B).

---

## Blocker yang Harus Ditagih ke Klien

- [ ] Data berat & volume master carton per produk → **memblokir Fase 5.2** (Fase 2.7 jalan lebih dulu dengan angka sementara di `ProductSeeder`)
- [ ] File PDF COA, MSDS, SHT → memblokir Fase 2.4
- [ ] Foto & video produksi/QC/stuffing (dengan izin resmi) → memblokir Fase 5.2
- [ ] Terjemahan Arab & Rusia → memblokir Fase 6
- [ ] Logo, brand asset, dan data legalitas → memblokir Fase 4.3
- [ ] Konfirmasi tertulis: sample Djavacoal sebagai referensi tampilan atau fitur → **memblokir finalisasi scope**
