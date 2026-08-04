# 05 — Design System

## Prinsip

1. **Tidak ada dark mode.** Tidak ada toggle terang/gelap di mana pun — admin maupun publik. Website publik memang bertema gelap secara desain, tapi itu palet tetap, bukan mode yang bisa dialihkan.
2. **Satu font: Poppins**, di-host lokal. Tidak ada Google Fonts CDN, tidak ada font kedua.
3. **Seluruh warna dapat dikustomisasi** lewat menu Tema di admin panel. Nilai di dokumen ini adalah preset bawaan, bukan nilai mati.
4. **Tidak ada tombol transparan.** Semua tombol aksi memakai latar berwarna solid.

---

## 1. Tipografi

### Font

**Poppins** — satu-satunya font di seluruh sistem. Weight yang dimuat: 300, 400, 500, 600, 700.

Di-host lokal di `resources/fonts/poppins/`, dimuat lewat `@font-face` dengan `font-display: swap`. Format WOFF2 saja.

```css
@font-face {
  font-family: 'Poppins';
  src: url('/fonts/poppins/poppins-400.woff2') format('woff2');
  font-weight: 400;
  font-style: normal;
  font-display: swap;
}
/* ulangi untuk 300, 500, 600, 700 */
```

Preload dua weight yang paling awal terlihat:

```html
<link rel="preload" href="/fonts/poppins/poppins-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/fonts/poppins/poppins-600.woff2" as="font" type="font/woff2" crossorigin>
```

> **Catatan Bahasa Arab:** Poppins tidak memiliki glyph Arab. Untuk locale `ar`, muat **Poppins Arabic** bila tersedia dalam lisensi yang sesuai; bila tidak, gunakan font Arab dengan proporsi serupa yang di-host lokal, dan hanya dimuat saat locale `ar` aktif. Jangan memuat font Arab untuk pengunjung berbahasa Inggris atau Rusia.

### Skala

| Peran | Ukuran | Weight | Line height | Kelas Tailwind |
|-------|--------|--------|-------------|----------------|
| Judul halaman | 24px | 600 | 1.25 | `text-2xl font-semibold` |
| Deskripsi halaman | 14px | 400 | 1.6 | `text-sm text-muted-foreground` |
| Judul Card | 16px | 600 | 1.4 | `text-base font-semibold` |
| Deskripsi Card | 13px | 400 | 1.55 | `text-[13px] text-muted-foreground` |
| Label form | 13px | 500 | 1.4 | `text-[13px] font-medium` |
| Input | 14px | 400 | 1.5 | `text-sm` |
| Teks tabel | 14px | 400 | 1.5 | `text-sm` |
| Header tabel | 12px | 600 | 1.4 | `text-xs font-semibold uppercase tracking-wide` |
| Tombol | 13px | 500 | 1 | `text-[13px] font-medium` |
| Helper / error | 12px | 400 | 1.5 | `text-xs` |
| Badge | 11px | 500 | 1 | `text-[11px] font-medium` |

Poppins memiliki tinggi-x yang besar dan terasa lebih besar dari font lain pada ukuran piksel yang sama. Karena itu skala di atas satu tingkat lebih kecil dibanding skala umum — jangan dinaikkan.

Angka di dalam tabel wajib `font-variant-numeric: tabular-nums`, terutama kolom berat, volume, tonase, dan Rupiah.

Judul publik boleh memakai `font-weight: 700` dengan `letter-spacing: -0.02em` agar tetap punya karakter tanpa font kedua.

---

## 2. Sistem Warna

Seluruh warna didefinisikan sebagai CSS variable, diinjeksi dari database saat render. Tailwind v4 membaca variable ini, sehingga mengganti tema tidak memerlukan build ulang.

```css
/* resources/css/app.css */
@theme {
  --color-primary:              var(--t-primary, #C94A17);
  --color-primary-foreground:   var(--t-primary-foreground, #FFFFFF);
  --color-secondary:            var(--t-secondary, #F5F5F3);
  --color-secondary-foreground: var(--t-secondary-foreground, #131418);
  --color-accent:               var(--t-accent, #96741A);
  --color-accent-foreground:    var(--t-accent-foreground, #FFFFFF);

  --color-background:           var(--t-background, #F7F7F4);
  --color-foreground:           var(--t-foreground, #131418);
  --color-card:                 var(--t-card, #FFFFFF);
  --color-card-foreground:      var(--t-card-foreground, #131418);
  --color-muted:                var(--t-muted, #F1F1EE);
  --color-muted-foreground:     var(--t-muted-foreground, #6B6E74);
  --color-border:               var(--t-border, #E1E1DA);
  --color-input:                var(--t-input, #E1E1DA);
  --color-ring:                 var(--t-ring, #C94A17);

  --color-success:              var(--t-success, #15803D);
  --color-success-foreground:   var(--t-success-foreground, #FFFFFF);
  --color-destructive:          var(--t-destructive, #B91C1C);
  --color-destructive-foreground: var(--t-destructive-foreground, #FFFFFF);
  --color-warning:              var(--t-warning, #B45309);
  --color-warning-foreground:   var(--t-warning-foreground, #FFFFFF);
  --color-info:                 var(--t-info, #1D4ED8);
  --color-info-foreground:      var(--t-info-foreground, #FFFFFF);
  --color-neutral:              var(--t-neutral, #4B5563);
  --color-neutral-foreground:   var(--t-neutral-foreground, #FFFFFF);
  --color-violet:               var(--t-violet, #6D28D9);
  --color-violet-foreground:    var(--t-violet-foreground, #FFFFFF);
  --color-teal:                 var(--t-teal, #0F766E);
  --color-teal-foreground:      var(--t-teal-foreground, #FFFFFF);

  --color-sidebar:              var(--t-sidebar, #FAFAF8);
  --color-sidebar-foreground:   var(--t-sidebar-foreground, #131418);
  --color-sidebar-accent:       var(--t-sidebar-accent, #F0EFEB);
  --color-sidebar-border:       var(--t-sidebar-border, #E1E1DA);

  --radius: var(--t-radius, 0.5rem);
}
```

Nilai `--t-*` diinjeksi server sebagai blok `<style>` di `<head>`. Bila tidak ada tema aktif, fallback pada nilai default di atas.

Pola ini penting: Tailwind v4 mengompilasi `bg-primary` menjadi `background-color: var(--color-primary)`. Karena `--color-primary` sendiri menunjuk ke `--t-primary`, mengganti `--t-primary` saat runtime langsung mengubah seluruh tampilan tanpa build ulang. Modifier opacity seperti `bg-primary/10` tetap bekerja karena Tailwind v4 memakai `color-mix()`.

Detail lengkap mekanisme tema: [10-theming.md](./10-theming.md).

### Preset Bawaan

**Ember Light** (default admin)

| Token | Nilai |
|-------|-------|
| primary | `#C94A17` |
| accent | `#96741A` |
| background | `#F7F7F4` |
| foreground | `#131418` |
| card | `#FFFFFF` |
| border | `#E1E1DA` |

**Charcoal Industrial** (default publik, sesuai brief)

| Token | Nilai |
|-------|-------|
| primary | `#E8622A` |
| accent | `#C9A227` |
| background | `#0B0B0C` |
| foreground | `#D9D6CE` |
| card | `#141518` |
| border | `#2C2F36` |

Preset lain: Slate Neutral, Forest Green, Navy Corporate. Seluruhnya bisa diduplikasi lalu dimodifikasi.

---

## 3. Sistem Tombol

Aturan di bagian ini bersifat mengikat. Tombol adalah elemen yang paling sering muncul, sehingga inkonsistensinya paling cepat terlihat.

### 3.1 Aturan Pokok

1. **Tidak ada tombol transparan.** Varian `ghost` dan `outline` tidak dipakai untuk tombol aksi. Setiap tombol memiliki latar berwarna solid.
2. **Setiap tombol wajib memuat ikon dan teks.** Tidak ada tombol ikon polos, kecuali tombol tutup (×) pada dialog dan tombol utilitas di dalam input.
3. **Dalam satu halaman, dua tombol dengan aksi berbeda tidak boleh berwarna sama.** Warna adalah penanda aksi, bukan hiasan.
4. Warna ditentukan oleh **jenis aksi**, bukan oleh posisi atau tingkat kepentingan.

### 3.2 Peta Aksi ke Warna

Peta ini bersifat tetap. Jangan memilih warna secara ad hoc.

| Aksi | Varian | Warna preset | Ikon lucide |
|------|--------|--------------|-------------|
| **Ubah / Edit** | `edit` | Hijau `#15803D` | `SquarePen` |
| **Hapus / Delete** | `delete` | Merah `#B91C1C` | `Trash2` |
| **Detail / Lihat** | `detail` | Amber `#B45309` | `Eye` |
| Tambah / Buat baru | `create` | Primary (jingga bata) | `Plus` |
| Simpan | `save` | Primary | `Save` |
| Batal | `cancel` | Neutral `#4B5563` | `X` |
| Kembali | `back` | Neutral `#4B5563` | `ArrowLeft` |
| Duplikat | `duplicate` | Violet `#6D28D9` | `Copy` |
| Ekspor / Unduh | `export` | Info `#1D4ED8` | `Download` |
| Unggah | `upload` | Info `#1D4ED8` | `Upload` |
| Aktifkan / Nonaktifkan | `toggle` | Teal `#0F766E` | `Power` |
| Terbitkan | `publish` | Teal `#0F766E` | `Send` |
| Arsipkan | `archive` | Neutral `#4B5563` | `Archive` |
| Terapkan filter | `filter` | Info `#1D4ED8` | `Filter` |
| Reset filter | `reset` | Neutral `#4B5563` | `RotateCcw` |
| Kirim WhatsApp | `whatsapp` | Success (hijau) | `MessageCircle` |

Karena Batal, Kembali, Reset, dan Arsipkan sama-sama Neutral, ketiganya **tidak boleh muncul bersamaan** dalam satu halaman. Dalam praktiknya memang tidak pernah — Batal hanya di form, Reset hanya di toolbar filter, Arsipkan hanya di baris tabel.

Bila suatu halaman benar-benar membutuhkan dua aksi Neutral sekaligus, salah satunya dipindah ke dropdown "Aksi lain".

### 3.3 Implementasi

```tsx
// resources/js/components/ui/button.tsx — perluasan varian shadcn
import { cva, type VariantProps } from 'class-variance-authority'

export const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 rounded-[var(--radius)] font-medium ' +
  'text-[13px] whitespace-nowrap transition-colors ' +
  'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ' +
  'disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-4 [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        create:    'bg-primary text-primary-foreground hover:brightness-110',
        save:      'bg-primary text-primary-foreground hover:brightness-110',
        edit:      'bg-success text-success-foreground hover:brightness-110',
        delete:    'bg-destructive text-destructive-foreground hover:brightness-110',
        detail:    'bg-warning text-warning-foreground hover:brightness-110',
        export:    'bg-info text-info-foreground hover:brightness-110',
        upload:    'bg-info text-info-foreground hover:brightness-110',
        filter:    'bg-info text-info-foreground hover:brightness-110',
        duplicate: 'bg-violet text-violet-foreground hover:brightness-110',
        toggle:    'bg-teal text-teal-foreground hover:brightness-110',
        publish:   'bg-teal text-teal-foreground hover:brightness-110',
        cancel:    'bg-neutral text-neutral-foreground hover:brightness-110',
        back:      'bg-neutral text-neutral-foreground hover:brightness-110',
        reset:     'bg-neutral text-neutral-foreground hover:brightness-110',
        archive:   'bg-neutral text-neutral-foreground hover:brightness-110',
        whatsapp:  'bg-success text-success-foreground hover:brightness-110',
      },
      size: {
        sm:   'h-8 px-3',
        default: 'h-9 px-4',
        lg:   'h-10 px-5',
      },
    },
    defaultVariants: { variant: 'create', size: 'default' },
  },
)
```

Perhatikan: **tidak ada varian `default`, `ghost`, `outline`, `secondary`, atau `link`**. Varian bawaan shadcn dihapus agar tidak ada jalan pintas untuk membuat tombol transparan.

Efek hover memakai `brightness-110`, bukan warna hover terpisah. Ini penting karena warna berasal dari tema yang bisa diganti — mendefinisikan warna hover per varian akan pecah saat tema berubah.

### 3.4 Komponen Siap Pakai

Untuk mencegah salah pilih varian atau ikon, sediakan komponen aksi yang sudah terikat:

```tsx
// resources/js/components/shared/action-buttons.tsx
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/react'
import { SquarePen, Trash2, Eye, Plus, Save, X } from 'lucide-react'

export function EditButton({ href, size = 'sm' }: { href: string; size?: 'sm' | 'default' }) {
  return (
    <Button asChild variant="edit" size={size}>
      <Link href={href}><SquarePen /> Ubah</Link>
    </Button>
  )
}

export function DetailButton({ href, size = 'sm' }: { href: string; size?: 'sm' | 'default' }) {
  return (
    <Button asChild variant="detail" size={size}>
      <Link href={href}><Eye /> Detail</Link>
    </Button>
  )
}

export function DeleteButton({ onClick, size = 'sm' }: { onClick: () => void; size?: 'sm' | 'default' }) {
  return (
    <Button variant="delete" size={size} onClick={onClick}>
      <Trash2 /> Hapus
    </Button>
  )
}
```

Aksi baris tabel wajib memakai komponen ini, bukan menyusun `<Button>` manual.

### 3.5 Aksi di Baris Tabel

Tiga tombol berdampingan, urutan tetap:

```
[ Detail (amber) ] [ Ubah (hijau) ] [ Hapus (merah) ]
```

Semuanya `size="sm"` dengan ikon dan teks. Bila kolom terlalu sempit di layar kecil, kolom aksi menjadi sticky di sisi akhir dan tabel dapat digeser horizontal — **bukan** diubah menjadi dropdown ikon.

Aksi tambahan di luar ketiganya (Duplikat, Arsipkan, Aktifkan) masuk ke dropdown "Aksi lain" agar baris tidak penuh.

### 3.6 Hierarki Tanpa Bantuan Warna

Karena semua tombol solid, hierarki visual tidak bisa mengandalkan bobot warna. Hierarki dibangun lewat:

- **Posisi** — aksi utama halaman di kanan atas header (kiri atas saat RTL)
- **Ukuran** — aksi utama `size="default"`, aksi baris `size="sm"`
- **Jarak** — kelompok aksi dipisah jarak 8px, kelompok berbeda dipisah 16px
- **Jumlah** — maksimal 3 tombol berdampingan; sisanya masuk dropdown

Ini adalah konsekuensi yang harus diterima dari aturan "tanpa transparan": halaman dengan banyak tombol akan terasa ramai. Karena itu batas 3 tombol berdampingan bersifat wajib, bukan saran.

---

## 4. Dialog

Seluruh aksi destruktif dan aksi yang mengubah status secara signifikan **wajib** melalui dialog konfirmasi. Tidak ada penghapusan langsung dari klik tombol.

### 4.1 Aksi yang Wajib Berdialog

- Hapus (semua entitas)
- Arsipkan
- Nonaktifkan perwakilan resmi
- Terbitkan / batalkan penerbitan artikel
- Aksi massal apa pun
- Aktifkan tema
- Hapus file dari media library
- Nonaktifkan 2FA
- Hapus atau nonaktifkan pengguna

### 4.2 Anatomi Dialog

```tsx
<ConfirmDialog
  open={open}
  onOpenChange={setOpen}
  tone="danger"
  icon={Trash2}
  title='Hapus produk "Shisha Premium 26mm"?'
  description="Produk ini akan dihapus beserta spesifikasi dan dokumennya. Data masih bisa dipulihkan lewat arsip dalam 30 hari."
  confirmLabel="Hapus Produk"
  confirmVariant="delete"
  cancelLabel="Batal"
  onConfirm={handleDelete}
  loading={processing}
/>
```

Aturan isi:

| Bagian | Aturan |
|--------|--------|
| Ikon | Lingkaran berlatar warna tone dengan opacity rendah, ikon di tengah |
| Judul | **Menyebut nama objeknya**. Bukan "Hapus data ini?" tapi `Hapus produk "Shisha Premium 26mm"?` |
| Deskripsi | Satu sampai dua kalimat: apa yang terjadi, dan apakah bisa dibatalkan |
| Tombol konfirmasi | Kata kerja spesifik ("Hapus Produk"), bukan "OK" atau "Ya" |
| Tombol batal | Selalu ada, varian `cancel`, berada sebelum tombol konfirmasi |

Tone yang tersedia: `danger` (merah), `warning` (amber), `info` (biru), `success` (hijau).

Saat proses berjalan, tombol konfirmasi menampilkan spinner dan berlabel "Menghapus...". Dialog tidak dapat ditutup selama proses berlangsung.

### 4.3 Dialog Hapus Massal

Menyebut jumlahnya dan mendaftarkan maksimal 5 nama pertama:

> **Hapus 12 produk terpilih?**
> Termasuk "Shisha Premium 26mm", "Shisha Standard 25mm", "BBQ Hexagonal 50mm", dan 9 lainnya. Seluruhnya akan dipindahkan ke arsip.

### 4.4 Dialog untuk Data Terkait

Bila objek tidak dapat dihapus karena masih dipakai, tetap tampilkan dialog — tetapi berupa dialog informasi dengan satu tombol tutup, bukan konfirmasi:

> **Kategori "Shisha" tidak dapat dihapus**
> Kategori ini masih dipakai oleh 4 produk. Pindahkan produk tersebut ke kategori lain terlebih dahulu.
> [ Lihat Produk Terkait ] [ Tutup ]

---

## 5. Spacing & Layout

Skala 4px. Nilai yang dipakai: `4 8 12 16 20 24 32 40 48 64`.

| Elemen | Nilai |
|--------|-------|
| Lebar sidebar | 260px (ciut 64px) |
| Tinggi header | 60px |
| Padding konten halaman | 24px (mobile 16px) |
| Padding dalam Card | 24px |
| Jarak antar Card | 20px |
| Jarak antar field form | 16px |
| Jarak label ke input | 6px |
| Jarak antar tombol sekelompok | 8px |
| Jarak antar kelompok tombol | 16px |
| Lebar maksimal form 1 kolom | 720px |
| Tinggi baris tabel | 52px (lebih tinggi dari standar karena baris memuat tiga tombol) |

Radius mengikuti token `--radius` dari tema. Card memakai `rounded-[calc(var(--radius)+2px)]`, tombol dan input memakai `rounded-[var(--radius)]`.

---

## 6. Elevation

Pemisahan utama memakai **border**, bukan shadow. Shadow hanya untuk elemen mengambang.

```css
--shadow-dropdown: 0 4px 12px rgb(0 0 0 / 0.08), 0 1px 3px rgb(0 0 0 / 0.06);
--shadow-dialog:   0 16px 48px rgb(0 0 0 / 0.16);
--shadow-toast:    0 8px 24px rgb(0 0 0 / 0.12);
```

---

## 7. Ikon

**lucide-react** saja. Jangan mencampur set lain.

| Konteks | Ukuran | Stroke |
|---------|--------|--------|
| Dalam tombol | 16px | 1.75 |
| Dalam tabel | 16px | 1.75 |
| Header Card | 18px | 1.75 |
| Sidebar | 20px | 1.75 |
| Empty state | 40px | 1.5 |
| Dialog | 20px | 1.75 |

Ikon per aksi mengikuti peta di bagian 3.2 dan tidak boleh diganti sesuka hati — konsistensi ikon sama pentingnya dengan konsistensi warna.

---

## 8. Badge Status

Badge memakai latar warna dengan opacity rendah dan teks berwarna penuh, sehingga tetap terbaca meski tema diganti.

| Status | Kelas |
|--------|-------|
| Aktif / Terbit | `bg-success/12 text-success` |
| Nonaktif / Draft | `bg-muted text-muted-foreground` |
| Terjadwal | `bg-info/12 text-info` |
| Arsip | `bg-neutral/12 text-neutral` |
| RFQ Baru | `bg-primary/12 text-primary` |
| RFQ Dihubungi | `bg-info/12 text-info` |
| RFQ Penawaran | `bg-warning/12 text-warning` |
| RFQ Negosiasi | `bg-warning/12 text-warning` |
| RFQ Menang | `bg-success/12 text-success` |
| RFQ Kalah | `bg-neutral/12 text-neutral` |
| RFQ Spam | `bg-destructive/12 text-destructive` |

Badge **tidak** memakai latar solid, agar tidak tertukar secara visual dengan tombol.

---

## 9. Indikator Kelengkapan Terjemahan

Tiga titik berlabel EN / AR / RU:

- Terisi penuh → `bg-success`
- Sebagian → `bg-warning`
- Kosong → `bg-border`

Muncul di kolom tabel dan di sisi tab bahasa pada form.

---

## 10. Empty State & Loading

**Empty state** wajib di setiap tabel dan daftar: ikon lucide 40px warna `muted-foreground`, judul singkat, satu kalimat penjelas, dan satu tombol aksi. Bedakan antara "belum ada data" (tombol Tambah) dan "filter tidak menemukan hasil" (tombol Reset filter).

**Loading:**
- Tabel → skeleton baris sebanyak `perPage`, bukan spinner
- Form submit → tombol disabled + spinner di dalam tombol, teks berubah ("Menyimpan...")
- Navigasi Inertia → progress bar tipis warna `primary` di puncak halaman
- Upload → progress bar per file dengan persentase

---

## 11. Bahasa Antarmuka

Admin panel memakai **Bahasa Indonesia**. Website publik memakai Inggris, Arab, dan Rusia.

| Prinsip | Benar | Salah |
|---------|-------|-------|
| Tombol memakai kata kerja spesifik | "Simpan Produk" | "Submit" |
| Nama aksi konsisten dari tombol ke notifikasi | "Terbitkan" → "Artikel diterbitkan" | "Publish" → "Berhasil disimpan" |
| Error menjelaskan cara memperbaiki | "Ukuran file 12 MB, maksimal 10 MB. Kompres dulu." | "Upload gagal" |
| Label memakai istilah pengguna | "Berat per master carton" | "master_carton_weight_kg" |
| Kalimat pendek | "Kategori ini masih dipakai 4 produk." | "Mohon maaf, sepertinya kategori yang Anda pilih..." |

Istilah perdagangan **tidak diterjemahkan**: Incoterms, FOB, CIF, HS Code, COA, MSDS, SHT, CBM, MOQ, Net Weight, Gross Weight, Master Carton.
