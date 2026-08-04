# 07 — Standar UI Admin Panel

Aturan di dokumen ini **wajib** dan berlaku untuk seluruh halaman admin tanpa kecuali. Bila sebuah halaman melanggar salah satunya, halaman itu belum selesai.

---

## 1. Anatomi Wajib Setiap Halaman

Setiap halaman admin **harus** memiliki, berurutan dari atas:

1. **Breadcrumb** — jalur navigasi dari Dashboard
2. **Title** — judul halaman
3. **Description** — satu kalimat penjelas fungsi halaman
4. **Card** — seluruh isi halaman dibungkus Card, tidak ada konten telanjang di atas background

```tsx
// resources/js/components/shared/page-header.tsx
import { Head, Link } from '@inertiajs/react'
import { ChevronRight } from 'lucide-react'

export interface Crumb {
  label: string
  href?: string
}

interface PageHeaderProps {
  title: string
  description: string
  breadcrumbs: Crumb[]
  actions?: React.ReactNode
}

export function PageHeader({ title, description, breadcrumbs, actions }: PageHeaderProps) {
  return (
    <>
      <Head title={title} />
      <nav aria-label="Breadcrumb" className="mb-4">
        <ol className="flex flex-wrap items-center gap-1.5 text-[13px] text-muted-foreground">
          {breadcrumbs.map((crumb, i) => (
            <li key={i} className="flex items-center gap-1.5">
              {crumb.href ? (
                <Link href={crumb.href} className="transition-colors hover:text-foreground">
                  {crumb.label}
                </Link>
              ) : (
                <span className="font-medium text-foreground">{crumb.label}</span>
              )}
              {i < breadcrumbs.length - 1 && (
                <ChevronRight className="size-3.5 shrink-0 rtl:rotate-180" aria-hidden />
              )}
            </li>
          ))}
        </ol>
      </nav>

      <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div className="space-y-1">
          <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
          <p className="text-sm text-muted-foreground">{description}</p>
        </div>
        {actions && <div className="flex items-center gap-2">{actions}</div>}
      </div>
    </>
  )
}
```

**Contoh pemakaian:**

```tsx
<PageHeader
  title="Produk"
  description="Kelola daftar produk briket beserta spesifikasi teknis dan dokumen sertifikasinya."
  breadcrumbs={[
    { label: 'Dashboard', href: dashboard.url() },
    { label: 'Produk' },
  ]}
  actions={
    <Button asChild>
      <Link href={products.create.url()}>
        <Plus className="size-4" /> Tambah Produk
      </Link>
    </Button>
  }
/>

<Card>
  <CardHeader>
    <CardTitle>Daftar Produk</CardTitle>
    <CardDescription>Total 12 produk, 9 aktif.</CardDescription>
  </CardHeader>
  <CardContent>
    <DataTable ... />
  </CardContent>
</Card>
```

**Aturan penulisan description:** jelaskan apa yang bisa dilakukan di halaman ini, bukan mengulang judul. Salah: "Halaman produk". Benar: "Kelola daftar produk briket beserta spesifikasi teknis dan dokumen sertifikasinya."

---

## 2. CRUD Tanpa Modal

Seluruh operasi tambah dan ubah memakai **halaman Inertia terpisah**, bukan dialog.

| Aksi | Rute |
|------|------|
| Daftar | `/admin/products` |
| Tambah | `/admin/products/create` |
| Ubah | `/admin/products/{id}/edit` |
| Detail | `/admin/products/{id}` (bila perlu) |

**Modal hanya boleh dipakai untuk:**
- Dialog konfirmasi destruktif
- Preview gambar atau dokumen
- Crop gambar
- Pemilih dari media library

Alasan: form produk memiliki puluhan field ditambah tab tiga bahasa dan repeater spesifikasi. Modal membuat URL tidak bisa dibagikan, tombol back browser menutup form dan menghilangkan isian, serta ruang vertikal jadi sempit.

---

## 3. DataTable

Basis: **shadcn/ui + TanStack Table v8**. Satu komponen `DataTable` generik dipakai ulang di seluruh modul.

### Wajib ada

| Fitur | Keterangan |
|-------|------------|
| Pagination server-side | Tidak pernah mengirim seluruh dataset |
| Pencarian global | Debounce 300 ms, sinkron ke query string |
| Filter per kolom | Sesuai konteks modul |
| Sorting server-side | Klik header, indikator arah |
| Pemilihan kolom | Dropdown "Kolom" untuk menyembunyikan |
| Pilihan jumlah baris | 10 / 15 / 25 / 50 / 100 |
| Bulk selection | Checkbox + aksi massal bila relevan |
| Skeleton loading | Sebanyak `perPage`, bukan spinner |
| Empty state | Berbeda antara "belum ada data" dan "filter tidak menemukan hasil" |
| State di URL | Filter, sort, dan halaman tersimpan di query string agar bisa di-bookmark dan bertahan setelah refresh |

### Kolom aksi

Berada di kolom paling akhir, **sticky** agar tetap terlihat saat tabel digeser horizontal.

Tiga tombol berdampingan dengan urutan tetap, semuanya `size="sm"` dengan ikon dan teks:

```
[ Detail (amber) ] [ Ubah (hijau) ] [ Hapus (merah) ]
```

Dilarang mengubahnya menjadi dropdown ikon `MoreHorizontal`. Aksi di luar ketiganya — Duplikat, Arsipkan, Aktifkan/Nonaktifkan — masuk ke dropdown "Aksi lain" yang ditempatkan setelah tombol Hapus.

Detail lengkap sistem tombol ada di [05-design-system.md](./05-design-system.md) bagian 3.

### Kolom yang sering dilupakan

- Kolom **kelengkapan terjemahan** untuk seluruh entitas translatable
- Kolom **urutan** dengan handle drag untuk entitas yang punya `sort_order`
- Kolom **status** memakai `StatusBadge`, bukan teks polos

---

## 4. Notifikasi — Sonner

Seluruh notifikasi memakai **Sonner**. Dilarang memakai alert bawaan browser atau komponen toast lain.

```tsx
// resources/js/hooks/use-flash-toast.ts
import { usePage } from '@inertiajs/react'
import { useEffect } from 'react'
import { toast } from 'sonner'

export function useFlashToast() {
  const { flash } = usePage().props as unknown as {
    flash: { success?: string; error?: string; warning?: string; info?: string }
  }

  useEffect(() => {
    if (flash?.success) toast.success(flash.success)
    if (flash?.error) toast.error(flash.error)
    if (flash?.warning) toast.warning(flash.warning)
    if (flash?.info) toast.info(flash.info)
  }, [flash])
}
```

Panggil sekali di `AdminLayout`.

### Aturan isi pesan

| Situasi | Pesan |
|---------|-------|
| Simpan berhasil | "Produk berhasil ditambahkan." |
| Ubah berhasil | "Perubahan produk berhasil disimpan." |
| Hapus berhasil | "Produk berhasil dihapus." |
| Validasi gagal | Tidak pakai toast — tampilkan error inline di bawah field |
| Error server | "Terjadi kesalahan saat menyimpan. Coba lagi atau hubungi administrator." |
| Aksi lama berjalan | `toast.promise()` dengan pesan loading, success, error |

Toast **tidak** dipakai untuk error validasi form. Error validasi ditampilkan inline agar pengguna tahu field mana yang bermasalah.

Posisi: `top-right`. Durasi: 4 detik untuk sukses, 6 detik untuk error. Toast error bisa ditutup manual.

---

## 5. Form

### 5.1 Struktur

Form panjang dibagi ke beberapa Card bertema, bukan satu Card raksasa:

```
Card: Informasi Dasar        (kode, kategori, urutan, status)
Card: Konten Multi-bahasa    (tab EN/AR/RU)
Card: Spesifikasi Teknis     (repeater)
Card: Data Pengiriman        (master carton, dimensi, MOQ)
Card: Media                  (sampul, galeri)
Card: Dokumen                (COA, MSDS, SHT)
Card: SEO                    (meta title, meta description)
```

Tombol aksi di **bagian bawah**, sticky saat form panjang:

```tsx
<div className="sticky bottom-0 -mx-6 -mb-6 flex justify-end gap-2 border-t bg-card/95 px-6 py-4 backdrop-blur">
  <Button variant="cancel" type="button" onClick={() => history.back()}>
    <X /> Batal
  </Button>
  <Button variant="save" type="submit" disabled={processing}>
    {processing ? <Loader2 className="animate-spin" /> : <Save />}
    {processing ? 'Menyimpan...' : 'Simpan Produk'}
  </Button>
</div>
```

Perhatikan `variant="cancel"` — bukan `outline`. Tombol transparan tidak dipakai di mana pun.

### 5.2 Input Rupiah

Wajib untuk seluruh field bernilai uang.

```tsx
// resources/js/components/form/input-rupiah.tsx
import { Input } from '@/components/ui/input'

interface InputRupiahProps {
  value: number | null
  onChange: (value: number | null) => void
  placeholder?: string
  disabled?: boolean
  id?: string
}

const format = (n: number) => new Intl.NumberFormat('id-ID').format(n)

export function InputRupiah({ value, onChange, placeholder = '0', disabled, id }: InputRupiahProps) {
  const display = value === null || Number.isNaN(value) ? '' : format(value)

  return (
    <div className="relative">
      <span className="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-sm text-muted-foreground">
        Rp
      </span>
      <Input
        id={id}
        inputMode="numeric"
        className="ps-10 text-end tabular-nums"
        value={display}
        placeholder={placeholder}
        disabled={disabled}
        onChange={(e) => {
          const digits = e.target.value.replace(/\D/g, '')
          onChange(digits === '' ? null : Number.parseInt(digits, 10))
        }}
      />
    </div>
  )
}
```

Nilai yang dikirim ke server adalah integer murni tanpa pemisah. Disimpan `BIGINT` tanpa desimal.

### 5.3 Date Picker

Wajib memakai komponen date picker, dilarang `<input type="date">` mentah.

Basis: `react-day-picker` + `date-fns` dengan locale `id`. Format tampilan `dd MMMM yyyy`. Untuk field yang butuh jam, tambahkan pemilih waktu terpisah di dalam popover yang sama.

Untuk filter rentang tanggal (RFQ, log aktivitas) pakai `DateRangePicker` dengan preset: Hari ini, 7 hari terakhir, 30 hari terakhir, Bulan ini, Bulan lalu, Kustom.

### 5.4 Select

**Aturan tegas:** bila jumlah opsi **lebih dari 10**, wajib memakai searchable select (pola select2), bukan `<Select>` biasa.

Implementasi: `Popover` + `Command` (cmdk) dari shadcn.

```tsx
// resources/js/components/form/combobox-select.tsx
// Wajib mendukung:
// - ketik untuk mencari
// - keyboard navigation (arrow, enter, escape)
// - opsi "Hapus pilihan" bila nullable
// - mode multiple dengan badge yang bisa dihapus
// - loading state untuk opsi yang diambil async
// - empty state: "Tidak ada hasil untuk 'xxx'"
```

Untuk daftar sangat panjang (misal pemilih negara, ~250 opsi), tambahkan virtualisasi agar tidak merender seluruh opsi sekaligus.

Rekap komponen select yang dipakai:

| Jumlah opsi | Komponen |
|-------------|----------|
| 2 | Radio group atau Switch |
| 3–10 | `Select` shadcn biasa |
| > 10 | `ComboboxSelect` (searchable) |
| > 100 | `ComboboxSelect` + virtualisasi |

### 5.5 Tab Multi-bahasa

```tsx
<Tabs defaultValue="en">
  <TabsList>
    <TabsTrigger value="en">
      English <TranslationDot status={completeness.en} />
    </TabsTrigger>
    <TabsTrigger value="ar">
      العربية <TranslationDot status={completeness.ar} />
    </TabsTrigger>
    <TabsTrigger value="ru">
      Русский <TranslationDot status={completeness.ru} />
    </TabsTrigger>
  </TabsList>
  {/* konten tab */}
</Tabs>
```

Aturan:
- Berpindah tab **tidak boleh** menghilangkan isian tab lain (semua tab dalam satu form state)
- Field Bahasa Inggris wajib; lainnya opsional
- Tab Arab memakai `dir="rtl"` pada input di dalamnya
- Tersedia tombol "Salin dari Bahasa Inggris" per tab
- Error validasi pada tab yang tidak aktif harus ditandai di label tab-nya, agar pengguna tahu ke mana harus pergi

### 5.6 Repeater

Untuk spesifikasi produk, item galeri, dan sejenisnya. Wajib mendukung tambah baris, hapus baris dengan konfirmasi, dan urut ulang drag-and-drop memakai `@dnd-kit`.

---

## 6. Upload File & Gambar

Ini bagian yang paling sering dikerjakan asal-asalan. Standarnya tinggi karena admin akan memakainya setiap hari.

### 6.1 Yang wajib ada

| Fitur | Detail |
|-------|--------|
| Drag & drop | Area dengan feedback visual saat file dilayangkan di atasnya |
| Klik untuk pilih | Alternatif untuk pengguna yang tidak terbiasa drag |
| Tempel dari clipboard | `Ctrl+V` langsung mengunggah screenshot |
| Pratinjau instan | `URL.createObjectURL()` sebelum unggah selesai |
| Progress per file | Persentase dan bar, memakai `onUploadProgress` |
| Batalkan | `AbortController` untuk membatalkan unggahan berjalan |
| Validasi sisi klien | Tipe, ukuran, dan dimensi minimal diperiksa **sebelum** request dikirim |
| Crop | Untuk field yang punya rasio wajib |
| Multi-file | Dengan pengurutan drag-and-drop setelahnya |
| Ganti file | Menghapus file lama dari storage secara otomatis |
| Pesan error spesifik | Menyebut penyebab dan cara memperbaikinya |

### 6.2 Aturan validasi

| Jenis | Tipe diterima | Maksimal | Dimensi minimal |
|-------|---------------|----------|-----------------|
| Sampul produk | jpg, jpeg, png, webp | 5 MB | 1200 × 900 |
| Galeri | jpg, jpeg, png, webp | 5 MB | 800 × 600 |
| Logo & ikon | png, webp, svg | 1 MB | 200 × 200 |
| Foto profil / tim | jpg, jpeg, png, webp | 2 MB | 400 × 400 |
| Dokumen | pdf, xlsx | 10 MB | — |
| Video | mp4, webm | 50 MB | — |

SVG hanya diterima untuk logo dan wajib disanitasi (SVG dapat memuat skrip).

### 6.3 Pesan error

| Situasi | Pesan |
|---------|-------|
| Ukuran berlebih | "Ukuran file 12,4 MB. Maksimal 5 MB. Kompres gambar terlebih dahulu." |
| Tipe salah | "Format .heic belum didukung. Gunakan JPG, PNG, atau WebP." |
| Dimensi kurang | "Resolusi gambar 640 × 480. Minimal 1200 × 900 agar tidak pecah saat ditampilkan." |
| Koneksi putus | "Unggahan terputus. Periksa koneksi lalu coba lagi." |
| Server menolak | "File ditolak server. Coba file lain atau hubungi administrator." |

Dilarang menampilkan "Upload gagal" tanpa penjelasan.

### 6.4 Pemrosesan sisi server

Memakai `spatie/laravel-medialibrary` dengan konversi:

```php
public function registerMediaConversions(?Media $media = null): void
{
    $this->addMediaConversion('thumb')
        ->fit(Fit::Crop, 300, 225)
        ->format('webp')
        ->quality(82)
        ->nonQueued();

    $this->addMediaConversion('medium')
        ->fit(Fit::Contain, 800, 600)
        ->format('webp')
        ->quality(85);

    $this->addMediaConversion('large')
        ->fit(Fit::Contain, 1600, 1200)
        ->format('webp')
        ->quality(85);
}
```

Konversi `thumb` dibuat langsung (`nonQueued`) agar pratinjau segera muncul di admin. Sisanya lewat queue.

Nama file disanitasi: huruf kecil, spasi jadi tanda hubung, karakter non-ASCII dihapus, ditambah hash pendek untuk mencegah tabrakan.

---

## 6A. Tombol — Ringkasan Aturan

Aturan penuh ada di [05-design-system.md](./05-design-system.md) bagian 3. Yang wajib diingat:

1. **Tidak ada tombol transparan.** Varian `ghost` dan `outline` dihapus dari `buttonVariants`.
2. **Setiap tombol memuat ikon dan teks.**
3. **Dalam satu halaman, dua aksi berbeda tidak boleh berwarna sama.**
4. Warna ditentukan jenis aksi: Ubah hijau, Hapus merah, Detail amber, Tambah/Simpan primary, Batal/Kembali/Reset neutral, Ekspor/Filter info, Duplikat violet, Aktifkan/Terbitkan teal.
5. Maksimal **3 tombol berdampingan**. Sisanya masuk dropdown "Aksi lain".

Selalu pakai komponen `EditButton`, `DetailButton`, `DeleteButton` untuk aksi baris tabel — jangan menyusun `<Button>` manual, karena di situlah inkonsistensi warna dan ikon muncul.

---

## 6B. Dialog

Seluruh aksi destruktif dan aksi yang mengubah status secara signifikan **wajib** melalui dialog konfirmasi. Tidak ada penghapusan langsung dari klik tombol, dan tidak ada `window.confirm()`.

Aksi yang wajib berdialog: Hapus, Arsipkan, Nonaktifkan perwakilan, Terbitkan/batalkan terbit, seluruh aksi massal, Aktifkan tema, Hapus media, Nonaktifkan 2FA, Hapus/nonaktifkan pengguna.

Judul dialog **wajib menyebut nama objeknya**:

> Hapus produk "Shisha Premium 26mm"?

Bukan "Hapus data ini?". Tombol konfirmasi memakai kata kerja spesifik ("Hapus Produk"), bukan "OK" atau "Ya".

Anatomi lengkap dan varian tone ada di [05-design-system.md](./05-design-system.md) bagian 4.

---

## 7. Sidebar & Navigasi

Struktur menu, dikelompokkan:

```
Dashboard

PRODUK
  Produk
  Kategori Produk
  Tipe Kontainer
  Opsi Kemasan

PERUSAHAAN
  Sertifikat
  Proses Produksi
  Protokol QC
  Keunggulan
  Tim
  Mitra Pelayaran
  Perwakilan Resmi

KONTEN
  Artikel
  Kategori Artikel
  FAQ
  Galeri
  Blok Halaman

LEADS
  RFQ Inbox          [badge jumlah baru]
  Pesan Kontak       [badge jumlah baru]

SISTEM
  Media Library
  Pengaturan Situs
  Tema & Warna
  Manajemen Pengguna
  Log Aktivitas
```

Aturan:
- Menu aktif ditandai latar `sidebar-accent` dan garis aksen di sisi awal
- Grup menu yang memuat halaman aktif otomatis terbuka
- Badge angka pada RFQ dan Pesan Kontak diambil dari shared props Inertia, diperbarui tiap navigasi
- Sidebar bisa diciutkan; kondisinya disimpan di cookie (bukan localStorage)
- Di bawah 1024px sidebar menjadi Sheet yang dibuka lewat tombol hamburger

---

## 8. Responsif

Admin panel dipakai mayoritas dari laptop, tapi tetap harus berfungsi di tablet dan ponsel.

| Breakpoint | Perilaku |
|------------|----------|
| ≥ 1280px | Sidebar terbuka, tabel penuh, form 2 kolom |
| 1024–1279px | Sidebar bisa diciutkan, form 2 kolom |
| 768–1023px | Sidebar jadi Sheet, form 1 kolom, tabel bisa digeser horizontal |
| < 768px | Tabel berubah jadi daftar kartu; kolom prioritas rendah disembunyikan |

Pada tampilan kartu di ponsel, tampilkan maksimal 4 informasi per kartu ditambah tombol aksi.

---

## 9. Aksesibilitas

- Setiap input punya `<Label htmlFor>` yang benar
- Error dikaitkan lewat `aria-describedby` dan `aria-invalid`
- Fokus terlihat jelas: `focus-visible:ring-2 focus-visible:ring-ring`
- Dialog memerangkap fokus dan tertutup dengan `Escape`
- Ikon dekoratif diberi `aria-hidden`; ikon bermakna diberi `aria-label`
- Tombol ikon tanpa teks wajib punya `sr-only` label
- Kontras minimal 4.5:1 untuk teks normal

---

## 10. Checklist Sebelum Menandai Halaman Selesai

- [ ] Breadcrumb, title, dan description ada dan akurat
- [ ] Seluruh konten dibungkus Card
- [ ] Tabel memakai DataTable dengan pagination server-side
- [ ] Filter dan sort tersimpan di query string
- [ ] Empty state ada, dan membedakan kondisi kosong vs hasil filter
- [ ] Skeleton loading, bukan spinner, untuk tabel
- [ ] Notifikasi memakai Sonner dengan pesan Bahasa Indonesia yang spesifik
- [ ] Error validasi tampil inline, bukan toast
- [ ] Select dengan lebih dari 10 opsi memakai searchable select
- [ ] Field tanggal memakai date picker
- [ ] Field uang memakai InputRupiah
- [ ] Upload memakai komponen uploader modern lengkap dengan progress
- [ ] **Tidak ada tombol transparan** — seluruh tombol berlatar solid
- [ ] **Setiap tombol memuat ikon dan teks**
- [ ] **Tidak ada dua aksi berbeda dengan warna tombol yang sama** dalam satu halaman
- [ ] Maksimal 3 tombol berdampingan; sisanya di dropdown "Aksi lain"
- [ ] Aksi baris tabel memakai `DetailButton`, `EditButton`, `DeleteButton`
- [ ] Aksi destruktif punya dialog konfirmasi yang menyebut nama objeknya
- [ ] Halaman tetap benar setelah tema warna diganti (tidak ada warna hardcoded)
- [ ] Form tidak kehilangan isian saat validasi gagal
- [ ] Tab bahasa menampilkan indikator kelengkapan
- [ ] Responsif sampai 360px
- [ ] Navigasi keyboard berfungsi penuh
- [ ] `pint`, `phpstan`, `pest`, `tsc --noEmit`, dan `eslint` lolos
