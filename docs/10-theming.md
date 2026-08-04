# 10 — Sistem Tema (Custom Color Palette)

Admin dapat mengubah palet warna admin panel dan website publik tanpa menyentuh kode dan tanpa build ulang.

**Tidak ada dark mode.** Tema adalah palet warna yang bisa dipilih dan dikustomisasi, bukan mode terang/gelap yang bisa dialihkan pengunjung.

---

## 1. Cara Kerja

```
themes (DB)  →  ThemeService (cache)  →  shared props Inertia
                                      →  <style> injeksi di <head>
                                      →  CSS variable --t-*
                                      →  Tailwind @theme membaca --t-*
                                      →  seluruh utility class ikut berubah
```

Kuncinya ada pada dua lapis variable:

```css
@theme {
  --color-primary: var(--t-primary, #C94A17);
}
```

Tailwind v4 mengompilasi `bg-primary` menjadi `background-color: var(--color-primary)`. Karena `--color-primary` menunjuk ke `--t-primary`, mengganti `--t-primary` saat runtime langsung mengubah seluruh tampilan. Nilai setelah koma adalah fallback bila tema gagal dimuat.

### Kenapa tidak langsung menimpa `--color-primary`?

Bisa, tapi lapis kedua memberi dua keuntungan: ada nilai fallback yang aman bila database bermasalah, dan namespace `--t-*` memperjelas mana variable yang berasal dari tema dan mana yang bawaan sistem.

---

## 2. Injeksi ke Halaman

### Server

```php
// app/Services/ThemeService.php
namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;

final class ThemeService
{
    private const CACHE_TTL = 60 * 60 * 24 * 30;

    /** @return array<string, string> */
    public function tokensFor(string $scope): array
    {
        return Cache::remember(
            "theme.tokens.{$scope}",
            self::CACHE_TTL,
            function () use ($scope) {
                $column = $scope === 'admin' ? 'is_active_admin' : 'is_active_public';

                $theme = Theme::query()->where($column, true)->first()
                    ?? Theme::query()->where('slug', $scope === 'admin' ? 'ember-light' : 'charcoal-dark')->first();

                return $theme?->tokens ?? [];
            },
        );
    }

    /** @param array<string, string> $tokens */
    public function toCssVariables(array $tokens): string
    {
        $lines = [];

        foreach ($tokens as $key => $value) {
            $name = '--t-' . str_replace('_', '-', $key);
            $lines[] = "{$name}:{$value}";
        }

        return ':root{' . implode(';', $lines) . '}';
    }

    public function flush(): void
    {
        Cache::forget('theme.tokens.admin');
        Cache::forget('theme.tokens.public');
    }
}
```

Kirim lewat shared props:

```php
// HandleInertiaRequests::share()
'theme' => [
    'css' => $this->themes->toCssVariables(
        $this->themes->tokensFor($request->is('admin/*') ? 'admin' : 'public'),
    ),
],
```

### Blade root

```blade
{{-- resources/views/app.blade.php --}}
<head>
    @inertiaHead
    <style id="theme-tokens">{!! $page['props']['theme']['css'] !!}</style>
</head>
```

> **Penting:** blok `<style>` harus dirender **di server**, bukan lewat `useEffect`. Menyuntikkan tema setelah hidrasi menyebabkan kedipan warna default terlebih dahulu — sangat terlihat pada tema gelap dan langsung merusak kesan profesional.

Nilai token divalidasi ketat sebelum disimpan (hanya hex 6 digit), sehingga tidak ada risiko injeksi CSS lewat kolom database.

### Pratinjau langsung di editor tema

Hanya di halaman editor, tema boleh diterapkan lewat JavaScript untuk pratinjau seketika:

```ts
export function applyPreviewTokens(tokens: Record<string, string>) {
  const root = document.documentElement
  for (const [key, value] of Object.entries(tokens)) {
    root.style.setProperty(`--t-${key.replaceAll('_', '-')}`, value)
  }
}
```

Saat meninggalkan halaman tanpa menyimpan, kembalikan ke nilai semula dengan `removeProperty`.

---

## 3. Struktur Token

Lihat definisi lengkap di [04-erd.md](./04-erd.md) bagian `themes`. Ringkasnya, 33 token terbagi lima kelompok:

| Kelompok | Token |
|----------|-------|
| **Brand** | `primary`, `primary_foreground`, `secondary`, `secondary_foreground`, `accent`, `accent_foreground` |
| **Permukaan** | `background`, `foreground`, `card`, `card_foreground`, `muted`, `muted_foreground`, `border`, `input`, `ring` |
| **Semantik** | `success`, `destructive`, `warning`, `info`, `neutral`, `violet`, `teal` + masing-masing `_foreground` |
| **Sidebar** | `sidebar`, `sidebar_foreground`, `sidebar_accent`, `sidebar_border` |
| **Bentuk** | `radius` |

Token semantik **wajib ada** karena sistem tombol bergantung padanya: hijau untuk Ubah, merah untuk Hapus, amber untuk Detail. Bila admin mengubah `success` menjadi ungu, seluruh tombol Ubah ikut berubah. Ini disengaja — itulah gunanya tema.

---

## 4. Validasi

```php
// app/Http/Requests/Admin/Theme/StoreThemeRequest.php
public function rules(): array
{
    $hex = ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'];

    return [
        'name'  => ['required', 'string', 'max:100'],
        'scope' => ['required', 'in:admin,public,both'],
        'tokens'                => ['required', 'array'],
        'tokens.primary'        => $hex,
        'tokens.primary_foreground' => $hex,
        // ... seluruh token warna
        'tokens.radius' => ['required', 'string', 'regex:/^\d+(\.\d+)?(rem|px)$/'],
    ];
}
```

Aturan tambahan:

1. **Hanya hex 6 digit.** Tolak `rgb()`, `hsl()`, nama warna CSS, dan hex 3 digit. Ini menjaga konsistensi dan menutup celah injeksi.
2. **Kunci tak dikenal ditolak.** Bandingkan dengan daftar kunci tetap; kunci di luar daftar menyebabkan validasi gagal.
3. **Seluruh kunci wajib ada.** Token yang hilang membuat sebagian UI kembali ke fallback dan terlihat tidak konsisten.

### Pemeriksaan kontras

Ini yang membedakan fitur tema yang berguna dari yang berbahaya. Tanpa pemeriksaan kontras, admin bisa memilih kuning muda untuk `primary` dan seluruh tombol utama menjadi tidak terbaca.

```php
// app/Support/Color.php
final class Color
{
    public static function relativeLuminance(string $hex): float
    {
        [$r, $g, $b] = self::toRgb($hex);

        $channel = static fn (float $c): float => ($c /= 255) <= 0.03928
            ? $c / 12.92
            : (($c + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }

    public static function contrastRatio(string $a, string $b): float
    {
        $la = self::relativeLuminance($a);
        $lb = self::relativeLuminance($b);

        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /** Pilih hitam atau putih yang paling kontras terhadap $background. */
    public static function bestForeground(string $background): string
    {
        return self::contrastRatio($background, '#FFFFFF') >= self::contrastRatio($background, '#111111')
            ? '#FFFFFF'
            : '#111111';
    }

    /** @return array<int, string> */
    private static function toRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (float) hexdec(substr($hex, 0, 2)),
            (float) hexdec(substr($hex, 2, 2)),
            (float) hexdec(substr($hex, 4, 2)),
        ];
    }
}
```

Aturan penerapan:

| Pasangan | Rasio minimum | Perlakuan bila gagal |
|----------|---------------|----------------------|
| `foreground` ↔ `background` | 4.5 | **Tolak simpan** |
| `card_foreground` ↔ `card` | 4.5 | **Tolak simpan** |
| `primary_foreground` ↔ `primary` | 4.5 | **Tolak simpan** |
| Seluruh `*_foreground` ↔ pasangannya | 4.5 | **Tolak simpan** |
| `muted_foreground` ↔ `muted` | 3.5 | Peringatan, tetap boleh disimpan |
| `border` ↔ `background` | 1.4 | Peringatan |

Pesan penolakan menyebut angkanya:

> Kontras teks tombol utama terhadap warna primary hanya 2,1:1. Minimal 4,5:1 agar terbaca. Coba gelapkan warna primary atau ubah warna teksnya.

Sediakan tombol **"Perbaiki otomatis"** yang mengganti `*_foreground` memakai `Color::bestForeground()`. Ini menyelesaikan mayoritas kasus dalam satu klik.

---

## 5. Editor Tema di Admin

Halaman: `/admin/settings/themes`

### Halaman daftar

DataTable berisi kolom: nama, scope, pratinjau palet (6 lingkaran warna), penanda aktif untuk admin dan publik, penanda preset, aksi.

Aksi baris: **Detail** (amber), **Ubah** (hijau, nonaktif untuk preset), **Hapus** (merah, nonaktif untuk preset), ditambah dropdown berisi Duplikat, Aktifkan untuk Admin, Aktifkan untuk Publik.

### Halaman editor

Tata letak dua kolom:

```
┌────────────────────────────┬──────────────────────────────┐
│ Card: Informasi Tema       │ Card: Pratinjau Langsung     │
│  nama, scope               │  ┌────────────────────────┐  │
│                            │  │ mini admin panel palsu │  │
│ Card: Warna Brand          │  │ sidebar + card + tabel │  │
│  primary  [swatch][hex]    │  │ + 6 tombol semua warna │  │
│  accent   [swatch][hex]    │  │ + badge status         │  │
│                            │  │ + form field           │  │
│ Card: Permukaan            │  └────────────────────────┘  │
│  background, card, border  │                              │
│                            │  Card: Pemeriksaan Kontras   │
│ Card: Warna Semantik       │   foreground/background 12:1 │
│  success (Ubah)            │   primary/putih        4,8:1 │
│  destructive (Hapus)       │   success/putih        5,1:1 │
│  warning (Detail)          │   ...                        │
│  info, neutral, violet, teal│  [ Perbaiki otomatis ]      │
│                            │                              │
│ Card: Sidebar & Bentuk     │                              │
└────────────────────────────┴──────────────────────────────┘
```

Kolom kanan bersifat sticky agar pratinjau selalu terlihat saat menggulir.

Setiap field warna terdiri dari: swatch yang bisa diklik untuk membuka color picker, input hex yang bisa diketik manual, dan label yang menjelaskan di mana warna itu dipakai — misalnya `success` diberi keterangan "Tombol Ubah, badge Aktif".

Keterangan ini penting. Tanpa itu admin tidak tahu bahwa mengubah `warning` akan mengubah warna semua tombol Detail.

### Pratinjau

Pratinjau merender komponen nyata, bukan gambar: sidebar, Card dengan judul dan deskripsi, satu baris tabel lengkap dengan tiga tombol aksi, semua varian tombol, badge status, input form, dan dialog konfirmasi mini.

Diperbarui seketika saat nilai berubah, dengan debounce 150 ms.

### Aktivasi

Mengaktifkan tema wajib lewat dialog konfirmasi:

> **Aktifkan tema "Navy Corporate" untuk Admin Panel?**
> Seluruh pengguna admin akan langsung melihat palet warna baru pada halaman berikutnya yang dibuka.

```php
// app/Actions/Theme/ActivateThemeAction.php
public function handle(Theme $theme, string $scope): void
{
    DB::transaction(function () use ($theme, $scope) {
        $column = $scope === 'admin' ? 'is_active_admin' : 'is_active_public';

        Theme::query()->where($column, true)->update([$column => false]);
        $theme->update([$column => true]);

        $this->settings->set("active_theme_{$scope}", $theme->id);
    });

    $this->themes->flush();
}
```

Cache dikosongkan **setelah** transaksi selesai, bukan di dalamnya. Bila transaksi gagal setelah cache terhapus, pengguna sempat melihat tema yang salah.

---

## 6. Tema untuk Website Publik

Perbedaan penting: tema publik memengaruhi ribuan pengunjung, dan halaman publik di-cache.

**Aturan tambahan:**

1. Mengganti tema publik wajib mengosongkan cache halaman publik. Masukkan ke dalam `ActivateThemeAction`.
2. Preset `charcoal-dark` (sesuai brief) tidak dapat dihapus.
3. Tema publik memiliki token tambahan yang tidak dipakai admin:

| Token | Kegunaan |
|-------|----------|
| `hero_overlay` | Warna overlay di atas video/gambar hero, dengan alpha |
| `trust_bar_bg` | Latar bar trust badges |
| `footer_bg` | Latar footer |
| `footer_foreground` | Teks footer |

Token ini disimpan dalam JSON yang sama; tema ber-scope `admin` cukup mengabaikannya.

4. Bila `background` tema publik gelap (luminansi < 0.2), sistem otomatis menyarankan `foreground` berupa putih hangat `#D9D6CE`, bukan putih murni. Putih murni di atas hitam pekat menimbulkan halasi dan melelahkan mata pada teks panjang.

---

## 7. Yang Tidak Dapat Dikustomisasi

Agar sistem tetap konsisten dan tidak rusak:

- **Font** — Poppins, tetap
- **Skala tipografi** — tetap
- **Skala spacing** — tetap
- **Peta aksi ke jenis warna** — Ubah selalu memakai token `success`, Hapus selalu `destructive`, Detail selalu `warning`. Yang bisa diubah adalah *nilai* token tersebut, bukan pemetaannya
- **Ikon per aksi** — tetap
- **Bayangan** — tetap
- **Aturan "tanpa tombol transparan"** — tetap

Membiarkan admin mengubah pemetaan aksi ke warna akan menghancurkan konsistensi yang justru menjadi alasan aturan tombol dibuat.

---

## 8. Pengujian

- [ ] Mengganti tema mengubah warna tanpa build ulang
- [ ] Tidak ada kedipan warna default saat halaman pertama dimuat (tema dirender server-side)
- [ ] Validasi menolak hex 3 digit, `rgb()`, dan nama warna CSS
- [ ] Validasi menolak kunci token yang tidak dikenal
- [ ] Pemeriksaan kontras menolak kombinasi di bawah 4,5:1
- [ ] "Perbaiki otomatis" menghasilkan pasangan yang lolos kontras
- [ ] Hanya satu tema aktif per scope pada satu waktu
- [ ] Tema preset tidak dapat dihapus maupun diubah
- [ ] Cache halaman publik terhapus saat tema publik diganti
- [ ] Pratinjau kembali ke semula saat meninggalkan editor tanpa menyimpan
- [ ] Modifier opacity (`bg-primary/12`) tetap benar setelah tema diganti
- [ ] Perubahan tema tercatat di log aktivitas
