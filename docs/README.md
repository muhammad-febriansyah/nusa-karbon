# Dokumentasi Proyek — Website Ekspor Briket Arang Batok Kelapa

Paket dokumentasi untuk pengembangan website B2B ekspor briket arang batok kelapa dengan positioning **Supply Chain & Export Management Company** (trader, bukan pabrik).

**Nama kode proyek:** `Karbonesia`
**Versi dokumen:** 1.0
**Terakhir diperbarui:** 2026-08-02

---

## Daftar Dokumen

| No | Dokumen | Isi |
|----|---------|-----|
| 01 | [PRD](./01-prd.md) | Latar belakang, tujuan, scope, modul, non-functional requirements |
| 02 | [User Goals](./02-user-goals.md) | Persona dan tujuan tiap aktor |
| 03 | [User Stories](./03-user-stories.md) | User story + acceptance criteria per modul |
| 04 | [ERD & Skema Database](./04-erd.md) | Diagram relasi, definisi tabel, index, constraint |
| 05 | [Design System](./05-design-system.md) | Token warna, tipografi, spacing, komponen |
| 06 | [Arsitektur & Konvensi Kode](./06-architecture.md) | Service–Repository–Action, struktur folder, naming |
| 07 | [Standar UI Admin Panel](./07-admin-ui-standards.md) | Aturan wajib tiap halaman admin |
| 08 | [Strategi Multi-Bahasa](./08-multilingual.md) | EN/AR/RU, RTL, hreflang, routing |
| 09 | [Task Checklist](./09-tasks.md) | Breakdown pekerjaan, bisa ditandai per item |
| 10 | [Sistem Tema](./10-theming.md) | Kustomisasi palet warna admin & publik |

---

## Cara Pakai dengan Claude Code

1. Salin folder `docs/` dan file `CLAUDE.md` ke root repository.
2. Mulai sesi dengan membaca `docs/06-architecture.md` dan `docs/07-admin-ui-standards.md` lebih dulu — dua file itu yang paling sering dilanggar kalau tidak dibaca di awal.
3. Kerjakan berdasarkan `docs/09-tasks.md` secara berurutan. Tandai `[x]` setiap task selesai **dan** sudah lolos `pint` + `larastan` + `pest`.
4. Satu task = satu commit. Jangan gabung beberapa fase dalam satu commit.

---

## Prioritas Pengerjaan

```
FASE 1  Fondasi (auth, layout, design system, komponen dasar)   ← MULAI DI SINI
FASE 2  Admin Panel — Master Data
FASE 3  Admin Panel — Konten
FASE 4  Admin Panel — Leads & Pengaturan
FASE 5  Website Publik
FASE 6  Multi-bahasa & RTL
FASE 7  SEO, Performa, Hardening
```

Fase 1–4 adalah admin panel dan **wajib selesai lebih dulu**. Website publik tidak bisa diisi konten sebelum admin panel jalan.

---

## Ringkasan Keputusan Kunci

| Keputusan | Pilihan | Alasan |
|-----------|---------|--------|
| Role | **1 role: `admin`** | Tim kecil. Paket `spatie/laravel-permission` tetap dipasang agar penambahan role di masa depan tidak perlu refactor. |
| Multi-bahasa | Kolom JSON (`spatie/laravel-translatable`) | Hindari ledakan JOIN. Form admin cukup pakai tab per bahasa. |
| Media | `spatie/laravel-medialibrary` | Konversi otomatis, WebP, responsive images, polymorphic. |
| Frontend publik | Inertia + React (SSR aktif) | Satu stack dengan admin. SSR untuk SEO. |
| Dark mode | **Tidak ada** | Tidak ada toggle terang/gelap di mana pun. Tema publik memang gelap, tapi itu palet tetap. |
| Font | **Poppins**, di-host lokal | Satu font untuk admin dan publik. Dilarang Google Fonts CDN. |
| Palet warna | **Dapat dikustomisasi admin** | Tema terpisah untuk admin panel dan website publik, lengkap dengan pemeriksaan kontras. |
| Tombol | **Tanpa transparan**, warna per jenis aksi | Ubah hijau, Hapus merah, Detail amber. Dalam satu halaman tidak boleh ada dua aksi berwarna sama. |
| Aksi destruktif | **Wajib dialog konfirmasi** | Tidak ada `window.confirm()`. |
| CRUD | **Tanpa modal** — halaman Inertia terpisah | URL bisa dibagikan, back button berfungsi, form panjang lebih nyaman. |
| Soft delete | Aktif di semua master data & konten | Data ekspor tidak boleh hilang permanen karena salah klik. |

---

## Di Luar Scope Pengembangan

Hal-hal berikut **disediakan oleh klien**, bukan bagian dari pekerjaan developer:

- Foto dan video produksi, QC, stuffing kontainer
- Terjemahan Bahasa Arab dan Rusia (butuh penerjemah manusia, bukan mesin)
- File PDF COA, MSDS, SHT per produk
- Data teknis produk: berat & dimensi master carton, kapasitas kontainer
- Artikel blog
- Logo, brand asset, dan legalitas perusahaan
- Domain, hosting, dan biaya berjalan

**Container Calculator tidak dapat diselesaikan tanpa data teknis produk.** Pastikan data ini diterima sebelum Fase 2 dimulai.
