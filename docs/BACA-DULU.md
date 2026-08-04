# Paket Dokumentasi — Nusacarbon

Website B2B ekspor briket arang batok kelapa + admin panel.

## Isi

```
nusacarbon/
├── BACA-DULU.md          file ini
├── CLAUDE.md             konvensi untuk Claude Code — salin ke root repo
├── docs/                 11 dokumen — salin ke root repo
│   ├── README.md         indeks & ringkasan keputusan
│   ├── 01-prd.md         PRD
│   ├── 02-user-goals.md  persona & tujuan
│   ├── 03-user-stories.md user story + acceptance criteria
│   ├── 04-erd.md         ERD & skema database
│   ├── 05-design-system.md token, tipografi, sistem tombol, dialog
│   ├── 06-architecture.md Service-Repository-Action + contoh kode
│   ├── 07-admin-ui-standards.md aturan wajib tiap halaman admin
│   ├── 08-multilingual.md EN/AR/RU + RTL
│   ├── 09-tasks.md       checklist pekerjaan, tandai [x]
│   └── 10-theming.md     kustomisasi palet warna
└── mockups/              referensi visual website publik
    ├── landing-dark.html  versi dark industrial (sesuai brief awal)
    └── landing-light.html versi light corporate
```

## Cara pakai

1. Salin `docs/` dan `CLAUDE.md` ke root repository.
2. Mulai sesi Claude Code dengan membaca `docs/06-architecture.md` dan
   `docs/07-admin-ui-standards.md` — dua file itu yang paling sering
   dilanggar kalau tidak dibaca di awal.
3. Kerjakan berurutan dari `docs/09-tasks.md`, mulai Fase 1.1.
4. Tandai `[x]` hanya bila sudah lolos pint + phpstan + pest + tsc.

## Catatan mockup

Dua file di `mockups/` adalah referensi visual untuk **website publik**,
dibuat sebelum palet logo Nusacarbon final. Warnanya masih memakai
`#E8622A` / `#C94A17`, sedangkan logo memakai `#F93E00`.

Perlakukan sebagai referensi layout dan struktur, bukan referensi warna.
Warna final mengacu pada `docs/05-design-system.md`.

## Blocker yang harus ditagih ke klien

- Data berat & volume master carton per produk — memblokir Container Calculator
- File PDF COA, MSDS, SHT
- Foto & video produksi/QC/stuffing beserta izin resminya
- Terjemahan Arab & Rusia (penerjemah manusia)
- Konfirmasi tertulis: sample Djavacoal referensi tampilan atau fitur
