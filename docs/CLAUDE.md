# CLAUDE.md

Konvensi wajib untuk proyek ini. Baca file ini di awal setiap sesi.

## Proyek

Website B2B ekspor briket arang batok kelapa dengan admin panel. Klien adalah **trader / supply chain partner**, bukan pabrik — jangan pernah menulis copy yang mengklaim kepemilikan pabrik.

Dokumentasi lengkap ada di `docs/`. Yang paling sering dilanggar bila tidak dibaca:
- `docs/06-architecture.md` — Service, Repository, Action
- `docs/07-admin-ui-standards.md` — aturan wajib tiap halaman admin
- `docs/09-tasks.md` — urutan pengerjaan

## Stack

PHP 8.4 · Laravel 13 · Inertia 3 · React 19 + TypeScript · Tailwind v4 · shadcn/ui · MySQL 8 · Fortify · Wayfinder · Pest 4 · Larastan 3 · Pint

## Aturan yang Tidak Bisa Ditawar

### Setiap halaman admin
1. **Breadcrumb** dari Dashboard
2. **Title** halaman
3. **Description** satu kalimat
4. Seluruh konten dibungkus **Card**

Pakai komponen `PageHeader`. Jangan menulis breadcrumb manual di tiap halaman.

### Komponen wajib
| Kebutuhan | Komponen |
|-----------|----------|
| Tabel | `DataTable` (shadcn + TanStack), pagination server-side |
| Notifikasi | **Sonner** — tidak ada alternatif |
| Field uang | `InputRupiah` |
| Field tanggal | `DatePicker` — dilarang `<input type="date">` |
| Select ≤ 10 opsi | `Select` shadcn |
| Select > 10 opsi | `ComboboxSelect` (searchable, pola select2) |
| Upload gambar | `ImageUploader` / `MultiImageUploader` |
| Upload dokumen | `DocumentUploader` |
| Avatar | `AvatarUploader` (crop 1:1) |
| Konten multi-bahasa | `TranslatableTabs` |
| Aksi baris tabel | `DetailButton`, `EditButton`, `DeleteButton` |
| Konfirmasi | `ConfirmDialog` / `InfoDialog` / `BulkConfirmDialog` |

### Tombol — aturan mengikat
1. **Tidak ada tombol transparan.** Varian `ghost`, `outline`, `secondary`, `link`, dan `default` sudah **dihapus** dari `buttonVariants`. Kalau butuh tombol, pilih varian aksi.
2. **Setiap tombol memuat ikon dan teks.**
3. **Dalam satu halaman, dua aksi berbeda tidak boleh berwarna sama.**
4. Maksimal 3 tombol berdampingan; sisanya masuk dropdown "Aksi lain".

| Aksi | Varian | Warna |
|------|--------|-------|
| Ubah | `edit` | hijau |
| Hapus | `delete` | merah |
| Detail | `detail` | amber |
| Tambah / Simpan | `create` / `save` | primary |
| Batal / Kembali / Reset / Arsipkan | `cancel` / `back` / `reset` / `archive` | neutral |
| Ekspor / Unggah / Filter | `export` / `upload` / `filter` | info (biru) |
| Duplikat | `duplicate` | violet |
| Aktifkan / Terbitkan | `toggle` / `publish` | teal |

Aksi baris tabel selalu berurutan: `[Detail amber] [Ubah hijau] [Hapus merah]`.

### Dialog
Seluruh aksi destruktif dan perubahan status signifikan **wajib** lewat `ConfirmDialog`. **Dilarang `window.confirm()`.** Judul dialog wajib menyebut nama objeknya (`Hapus produk "Shisha Premium 26mm"?`), tombol konfirmasi memakai kata kerja spesifik, bukan "OK" atau "Ya".

### CRUD tanpa modal
Tambah dan ubah memakai **halaman Inertia terpisah**. Modal hanya untuk konfirmasi, preview, crop, dan pemilih media.

### Backend
- `declare(strict_types=1);` di seluruh file PHP
- Action, Service, Repository dideklarasikan `final`
- Controller tidak boleh berisi query Eloquent — panggil Repository atau Action
- Operasi multi-tabel dibungkus `DB::transaction()`
- Enum PHP untuk seluruh kolom ENUM database
- Pesan validasi **Bahasa Indonesia**
- Soft delete di seluruh master data dan konten; **tidak** di `rfq_requests` dan `contact_messages`
- Waktu disimpan UTC, ditampilkan WIB (`Asia/Jakarta`)
- Rupiah disimpan `BIGINT` tanpa desimal

### Frontend
- TypeScript strict, dilarang `any`
- Route lewat helper **Wayfinder**, bukan string URL
- **Dilarang `localStorage` dan `sessionStorage`** — pakai cookie atau state server
- Komponen di `components/ui/` tidak diedit manual
- Form memakai `useForm` Inertia
- **Logical properties saja**: `ms-`/`me-`/`ps-`/`pe-`/`start-`/`end-`/`text-start`/`text-end`. Dilarang `ml-`, `mr-`, `pl-`, `pr-`, `left-`, `right-`, `text-left`, `text-right` — situs ini mendukung RTL untuk Bahasa Arab

### Multi-bahasa
Tiga locale: `en` (default, tanpa prefix), `ar` (RTL), `ru`. Kolom translatable bertipe JSON via `spatie/laravel-translatable`.

Istilah yang **tidak diterjemahkan**: FOB, CIF, CFR, Incoterms, HS Code, COA, MSDS, SHT, CBM, MOQ, Net Weight, Gross Weight, Master Carton, Fixed Carbon, Ash Content, Calorific Value.

Angka, kode, email, dan istilah di atas selalu dibungkus `dir="ltr"` meski berada dalam konteks RTL.

### Pengguna & Role
**Satu role: `admin`.** `spatie/laravel-permission` tetap terpasang dan di-seed satu role agar penambahan role di masa depan tidak memerlukan refactor. Jangan membuat role kedua kecuali diminta.

Menu **Manajemen Pengguna** berisi CRUD **plus halaman detail**. Pengguna bisa mengganti avatar sendiri (crop 1:1). Pengguna tidak dapat menghapus atau menonaktifkan dirinya sendiri, dan admin aktif terakhir tidak dapat dihapus.

### Audit log
Wajib `LogsActivity` pada: `representatives`, `certificates`, `site_settings`, `themes`, `users`.

Model `representatives` menopang halaman anti-penipuan — setiap perubahan harus terlacak beserta nilai lama dan baru.

## Design Tokens & Tema

- **Tidak ada dark mode.** Tidak ada toggle terang/gelap, tidak ada kelas `dark:`, tidak ada konfigurasi `darkMode`. Tema publik memang gelap secara desain, tapi itu palet tetap.
- **Font: Poppins saja**, di-host lokal di `public/fonts/poppins/`. **Dilarang Google Fonts CDN.** Weight 300/400/500/600/700.
- **Seluruh warna dapat dikustomisasi admin** lewat menu Tema & Warna. Karena itu **dilarang menulis warna hardcoded** di komponen — selalu pakai token (`bg-primary`, `text-success`, `border-border`).

Token memakai dua lapis agar bisa diganti saat runtime tanpa build ulang:

```css
@theme { --color-primary: var(--t-primary, #C94A17); }
```

Nilai `--t-*` diinjeksi server sebagai `<style>` di `<head>` — **bukan lewat `useEffect`**, karena itu menyebabkan kedipan warna default saat load.

Preset default admin: `ember-light`. Preset default publik: `charcoal-dark`.

Detail: `docs/10-theming.md`.

## Perintah Verifikasi

Jalankan sebelum menandai task selesai:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
./vendor/bin/pest
npm run lint && npx tsc --noEmit
```

## Container Calculator

Logika inti proyek ini. Ada di `ContainerCalculatorService` (PHP) dan diduplikasi di `lib/container-calculator.ts` (TypeScript) agar responsif di sisi klien.

```
cartonByWeight = floor(container.max_payload_kg / product.master_carton_weight_kg)
cartonByVolume = floor(container.max_cbm / product.master_carton_cbm)
cartons        = min(cartonByWeight, cartonByVolume)
limitedBy      = cartonByWeight <= cartonByVolume ? 'weight' : 'volume'
```

Kedua implementasi **wajib menghasilkan angka identik**. Ada test yang memverifikasi ini.

Produk tanpa `master_carton_weight_kg` atau `master_carton_cbm` tidak boleh muncul di kalkulator. Tampilkan peringatan di admin bila data ini kosong.

## Yang Sering Salah

- Menulis kelas Tailwind arah fisik (`ml-4`) — akan rusak di RTL
- Membuat modal untuk form CRUD
- Lupa breadcrumb atau description
- Memakai toast untuk error validasi — error validasi tampil **inline**
- Query Eloquent langsung di Controller
- Mengirim seluruh dataset ke frontend alih-alih pagination server-side
- Pesan error generik ("Upload gagal") tanpa menyebut penyebab
- Lupa `dir="ltr"` pada angka dan kode di konteks Arab
- Menulis warna hardcoded (`bg-[#C94A17]`, `text-red-600`) — akan pecah saat tema diganti
- Memakai `variant="ghost"` atau `variant="outline"` — varian itu sudah dihapus
- Tombol ikon tanpa teks
- Dua aksi berbeda dengan warna tombol yang sama dalam satu halaman
- `window.confirm()` alih-alih `ConfirmDialog`
- Menyuntikkan tema lewat `useEffect` — menyebabkan kedipan warna saat load
