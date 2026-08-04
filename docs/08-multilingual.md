# 08 — Strategi Multi-Bahasa

Tiga bahasa: **Inggris** (default), **Arab**, **Rusia**.

Ini bagian yang paling sering diremehkan saat estimasi. Bahasa Arab bukan sekadar mengganti teks — layoutnya harus di-mirror sepenuhnya. Rencanakan dari awal; menambahkan RTL setelah seluruh UI jadi berarti menyentuh hampir semua file.

---

## 1. Konfigurasi Locale

```php
// config/locales.php
return [
    'default'   => 'en',
    'supported' => [
        'en' => ['name' => 'English',  'native' => 'English',  'dir' => 'ltr', 'flag' => 'gb'],
        'ar' => ['name' => 'Arabic',   'native' => 'العربية',  'dir' => 'rtl', 'flag' => 'sa'],
        'ru' => ['name' => 'Russian',  'native' => 'Русский',  'dir' => 'ltr', 'flag' => 'ru'],
    ],
];
```

---

## 2. Routing

Prefix berbasis URL. Bahasa default **tanpa prefix**.

```
/                       → English (default)
/products               → English
/ar/                    → Arab
/ar/products            → Arab
/ru/                    → Rusia
/ru/products            → Rusia
```

Menghilangkan prefix untuk bahasa default mencegah duplikasi konten di mata mesin pencari dan membuat URL utama lebih bersih.

```php
// routes/web.php
use App\Http\Middleware\SetLocale;

Route::middleware(SetLocale::class)->group(function () {
    Route::prefix('{locale?}')
        ->where(['locale' => 'ar|ru'])
        ->group(base_path('routes/public.php'));
});
```

```php
// app/Http/Middleware/SetLocale.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported'));
        $segment   = $request->segment(1);

        $locale = in_array($segment, $supported, true) && $segment !== config('locales.default')
            ? $segment
            : config('locales.default');

        App::setLocale($locale);
        $request->attributes->set('locale', $locale);

        return $next($request);
    }
}
```

**Admin panel tidak diberi prefix locale.** Antarmuka admin selalu Bahasa Indonesia; yang multi-bahasa adalah *isi konten*, bukan antarmukanya.

---

## 3. Penyimpanan Terjemahan

Memakai `spatie/laravel-translatable` dengan kolom JSON.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

final class Product extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var array<int, string> */
    public array $translatable = [
        'name',
        'slug',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
    ];
}
```

Fallback ke Bahasa Inggris bila terjemahan kosong:

```php
// config/translatable.php
'fallback_locale' => 'en',
```

### Slug per Locale

Slug ikut diterjemahkan agar SEO tiap bahasa optimal:

```json
{"en": "shisha-premium-26mm", "ar": "shisha-premium-26mm", "ru": "shisha-premium-26mm"}
```

Untuk Arab dan Rusia, **disarankan tetap memakai transliterasi Latin**, bukan aksara asli. Slug beraksara Arab atau Kiril akan ter-encode menjadi persen-encoding yang panjang dan jelek saat dibagikan, dan sebagian platform memotongnya.

Pencarian berdasarkan slug:

```php
public function findBySlugOrFail(string $slug, string $locale): Product
{
    return Product::query()
        ->where("slug->{$locale}", $slug)
        ->where('is_active', true)
        ->firstOrFail();
}
```

Validasi keunikan slug per locale dilakukan di level aplikasi lewat rule kustom, karena MySQL tidak bisa memberi unique index pada nilai di dalam JSON secara langsung (kecuali lewat generated column).

---

## 4. Layout RTL

### 4.1 Aturan Utama

Sejak baris pertama CSS ditulis, **gunakan logical properties**. Ini bukan optimasi, ini prasyarat.

| Dilarang | Gunakan |
|----------|---------|
| `ml-4` | `ms-4` |
| `mr-4` | `me-4` |
| `pl-6` | `ps-6` |
| `pr-6` | `pe-6` |
| `left-0` | `start-0` |
| `right-0` | `end-0` |
| `text-left` | `text-start` |
| `text-right` | `text-end` |
| `border-l` | `border-s` |
| `rounded-l-md` | `rounded-s-md` |

Tailwind v4 mendukung seluruh utility logis di atas secara bawaan.

Tambahkan aturan ESLint atau pemeriksaan CI untuk menolak kelas arah fisik pada file di `resources/js/`.

### 4.2 Penerapan Arah

```tsx
// resources/js/layouts/public-layout.tsx
export default function PublicLayout({ children, locale, dir }: Props) {
  useEffect(() => {
    document.documentElement.lang = locale
    document.documentElement.dir = dir
  }, [locale, dir])

  return <div className="min-h-screen">{children}</div>
}
```

Untuk SSR, atribut ini harus sudah benar di HTML awal — jangan hanya diatur lewat `useEffect`, karena akan menyebabkan pergeseran layout saat hidrasi.

### 4.3 Yang Harus Di-mirror

- Navigasi dan urutan menu
- Sidebar
- Ikon arah: panah, chevron breadcrumb, tombol carousel → `rtl:rotate-180`
- Urutan kolom tabel
- Progress bar dan slider
- Posisi toast Sonner → `top-left` saat RTL
- Arah animasi masuk

### 4.4 Yang TIDAK Boleh Di-mirror

Ini yang sering salah:

- **Angka** — tetap ditulis LTR
- **Kode HS, nomor sertifikat, nomor kontainer** — LTR
- **Istilah Incoterms** (FOB, CIF, CFR) — LTR
- **Alamat email dan URL** — LTR
- **Nilai spesifikasi** seperti `80% min`, `7,200 kcal/kg` — LTR
- **Nomor telepon** — LTR
- **Logo perusahaan** — jangan di-flip

Bungkus elemen-elemen ini:

```tsx
<span dir="ltr" className="inline-block tabular-nums">{value}</span>
```

Untuk tabel spesifikasi produk pada locale Arab, kolom parameter mengikuti RTL sedangkan kolom nilai tetap LTR. Ini terlihat sepele tapi sangat menentukan apakah halaman terbaca profesional atau terlihat seperti hasil terjemahan mesin.

---

## 5. Font

**Poppins** untuk seluruh sistem, di-host lokal. Tidak ada Google Fonts CDN dan tidak ada font kedua.

| Locale | Font |
|--------|------|
| en | Poppins |
| ru | Poppins (mencakup Kiril) |
| ar | Poppins Arabic bila lisensinya memungkinkan; bila tidak, font Arab pengganti dengan proporsi serupa, di-host lokal |

Poppins standar **tidak memiliki glyph Arab**. Siapkan file font Arab terpisah dan muat **hanya saat locale `ar` aktif**:

```php
// shared props
'arabic_font' => app()->getLocale() === 'ar',
```

```blade
@if($page['props']['arabic_font'])
    <link rel="preload" href="/fonts/poppins-arabic/regular.woff2" as="font" type="font/woff2" crossorigin>
@endif
```

Memuat font Arab untuk pengunjung berbahasa Inggris atau Rusia menambah beban tanpa manfaat, dan langsung terlihat di skor PageSpeed.

Teks Arab umumnya perlu `line-height` lebih longgar dan `letter-spacing` normal:

```css
[dir="rtl"] {
  line-height: 1.85;
  letter-spacing: 0;
}
```

Poppins memiliki tinggi-x besar, sehingga font Arab penggantinya harus dipilih yang proporsinya seimbang — bila terlalu kecil, teks Arab akan terlihat tenggelam dibanding versi Inggris pada ukuran yang sama.

## 6. SEO Multi-Bahasa

### 6.1 Hreflang

Setiap halaman wajib memuat tag hreflang lengkap termasuk `x-default`:

```html
<link rel="alternate" hreflang="en" href="https://example.com/products/shisha-premium" />
<link rel="alternate" hreflang="ar" href="https://example.com/ar/products/shisha-premium" />
<link rel="alternate" hreflang="ru" href="https://example.com/ru/products/shisha-premium" />
<link rel="alternate" hreflang="x-default" href="https://example.com/products/shisha-premium" />
```

Buat komponen `<HreflangTags>` yang menerima daftar alternatif dari server. Jangan menyusun URL secara manual di tiap halaman — itu sumber kesalahan yang sulit dilacak.

### 6.2 Canonical

Setiap halaman menunjuk ke dirinya sendiri pada locale yang sama, bukan ke versi Bahasa Inggris.

### 6.3 Sitemap

Sitemap terpisah per locale, dirujuk dari satu sitemap index:

```
/sitemap.xml           → index
/sitemap-en.xml
/sitemap-ar.xml
/sitemap-ru.xml
```

Halaman yang belum diterjemahkan **tidak dimasukkan** ke sitemap locale bersangkutan. Mengindeks halaman yang isinya fallback Bahasa Inggris justru merugikan peringkat.

### 6.4 Schema Markup

`Organization` di beranda, `Product` di halaman detail produk, `Article` di artikel, `BreadcrumbList` di semua halaman, `FAQPage` di halaman FAQ.

Field `inLanguage` diisi sesuai locale aktif.

---

## 7. Terjemahan Antarmuka

Teks UI publik (label tombol, judul seksi, pesan validasi) disimpan di file bahasa Laravel:

```
lang/
├── en/{site,form,validation}.php
├── ar/{site,form,validation}.php
└── ru/{site,form,validation}.php
```

Kirim ke frontend lewat shared props, bukan diimpor sebagai bundel JS terpisah:

```php
// HandleInertiaRequests
'translations' => cache()->rememberForever(
    "translations.{$locale}",
    fn () => [
        'site' => trans('site'),
        'form' => trans('form'),
    ],
),
```

Kosongkan cache saat file bahasa berubah (masukkan ke perintah deploy).

---

## 8. Istilah yang Tidak Diterjemahkan

Berlaku untuk ketiga bahasa:

`FOB` · `CIF` · `CFR` · `EXW` · `Incoterms` · `HS Code` · `COA` · `MSDS` · `SHT` · `CBM` · `MOQ` · `Net Weight` · `Gross Weight` · `Master Carton` · `Inner Box` · `20FT` · `40FT HC` · `Fixed Carbon` · `Ash Content` · `Calorific Value`

Ini standar perdagangan internasional. Buyer di Riyadh dan Moskow sama-sama memakai istilah Inggrisnya. Menerjemahkannya justru membuat halaman terlihat tidak dikerjakan oleh orang yang paham industri ini.

Sediakan glosarium untuk penerjemah agar mereka tidak "membantu" menerjemahkan istilah-istilah ini.

---

## 9. Alur Kerja Terjemahan

Klien menyediakan penerjemah manusia. Tugas developer adalah membuat prosesnya tidak menyakitkan:

1. Admin mengisi konten Bahasa Inggris terlebih dahulu
2. Tombol "Salin dari Bahasa Inggris" mengisi tab AR dan RU sebagai titik awal
3. Penerjemah menimpa isi tersebut
4. Indikator kelengkapan di daftar menunjukkan mana yang belum dikerjakan
5. Dashboard menampilkan ringkasan konten yang belum diterjemahkan

Bila di kemudian hari klien ingin melibatkan penerjemah lepas tanpa memberi akses penuh, tambahkan role kedua (`translator`) yang hanya bisa mengubah field terjemahan. Karena `spatie/laravel-permission` sudah terpasang sejak awal, ini hanya butuh seeder role baru dan penyesuaian Policy — bukan refactor.

---

## 10. Pengujian

- Setiap halaman publik dirender pada ketiga locale
- Locale Arab diperiksa pada lebar 360px, 768px, dan 1440px
- Angka, kode, dan istilah Incoterms tetap LTR di dalam konteks RTL
- Hreflang lengkap dan saling menunjuk dengan benar
- Fallback berfungsi: konten tanpa terjemahan menampilkan versi Bahasa Inggris, bukan halaman kosong
- Slug per locale dapat diakses dan unik
- Font Arab hanya dimuat saat locale `ar`, tidak ikut terunduh di locale lain
