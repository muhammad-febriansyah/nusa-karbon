# 06 — Arsitektur & Konvensi Kode

## 1. Stack

| Lapisan | Teknologi |
|---------|-----------|
| PHP | 8.4 |
| Framework | Laravel 13 |
| Auth | Laravel Fortify 1.x (+ 2FA) |
| Frontend bridge | Inertia.js 3 (`inertiajs/inertia-laravel`, `@inertiajs/react`) |
| UI | React 19 + TypeScript |
| Styling | Tailwind CSS v4 + shadcn/ui |
| Routing typed | Laravel Wayfinder + `@laravel/vite-plugin-wayfinder` |
| Database | MySQL 8.0+ |
| Static analysis | Larastan 3 |
| Formatting | Laravel Pint, Prettier 3, ESLint 9 |
| Testing | Pest 4 / PHPUnit 12 |
| Dev tooling | Laravel Boost, Laravel MCP, Pail, Sail, Prompts |

### Paket tambahan yang perlu dipasang

```bash
composer require spatie/laravel-permission
composer require spatie/laravel-medialibrary
composer require spatie/laravel-activitylog
composer require spatie/laravel-translatable
composer require spatie/laravel-sitemap
composer require mews/purifier
composer require intervention/image
```

```bash
npm i @tanstack/react-table sonner react-dropzone @dnd-kit/core @dnd-kit/sortable
npm i react-day-picker date-fns cmdk lucide-react
npm i @tiptap/react @tiptap/starter-kit @tiptap/extension-image @tiptap/extension-link
```

---

## 2. Arsitektur — Service, Repository, Action

Alur permintaan:

```
Route → Controller → FormRequest (validasi)
                  ↓
              Action  (satu operasi tulis, punya efek samping)
                  ↓
              Service (orkestrasi, logika bisnis, transaksi)
                  ↓
             Repository (akses data, query builder)
                  ↓
                Model
                  ↓
Controller → Inertia::render(Resource)
```

### Aturan pembagian tanggung jawab

| Lapisan | Boleh | Tidak boleh |
|---------|-------|-------------|
| **Controller** | Terima request, panggil Action/Service, kembalikan response | Query Eloquent, logika bisnis, validasi manual |
| **FormRequest** | Aturan validasi, pesan Bahasa Indonesia, otorisasi | Menyimpan data |
| **Action** | Satu operasi tulis yang bermakna secara bisnis. Punya `handle()` tunggal | Query kompleks, dipanggil dari view |
| **Service** | Orkestrasi beberapa repository, transaksi database, aturan bisnis | Menyentuh `Request` atau `Session` |
| **Repository** | Query, filter, pagination, eager loading | Logika bisnis, transaksi |
| **Model** | Relasi, cast, scope, accessor, trait | Query kompleks, logika bisnis |
| **Resource** | Bentuk data untuk frontend | Query tambahan (sumber N+1) |

**Kapan pakai Action, kapan pakai Service?**

Action untuk operasi tulis tunggal yang punya nama bisnis: `CreateProductAction`, `PublishPostAction`, `MarkRfqAsSpamAction`. Service untuk operasi yang mengoordinasi banyak hal atau dipakai berulang: `ContainerCalculatorService`, `SettingService`, `TranslationCompletenessService`.

CRUD sederhana cukup Action. Jangan membuat Service hanya untuk membungkus satu panggilan repository.

---

## 3. Struktur Folder

```
app/
├── Actions/
│   ├── Product/
│   │   ├── CreateProductAction.php
│   │   ├── UpdateProductAction.php
│   │   ├── DeleteProductAction.php
│   │   └── SyncProductSpecificationsAction.php
│   ├── Post/
│   ├── Rfq/
│   └── Representative/
├── Enums/
│   ├── RfqStatus.php
│   ├── PostStatus.php
│   ├── DocumentType.php
│   └── Incoterm.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   └── ...
│   │   └── Public/
│   │       ├── HomeController.php
│   │       ├── ProductController.php
│   │       └── ...
│   ├── Middleware/
│   │   ├── SetLocale.php
│   │   └── HandleInertiaRequests.php
│   ├── Requests/
│   │   └── Admin/Product/{StoreProductRequest,UpdateProductRequest}.php
│   └── Resources/
│       ├── Admin/ProductResource.php
│       └── Public/ProductResource.php
├── Models/
├── Observers/
├── Policies/
├── Repositories/
│   ├── Contracts/ProductRepositoryInterface.php
│   └── Eloquent/ProductRepository.php
├── Services/
│   ├── ContainerCalculatorService.php
│   ├── SettingService.php
│   ├── MediaUploadService.php
│   └── TranslationCompletenessService.php
├── Support/
│   ├── Locale.php
│   └── Rupiah.php
└── Traits/
    └── HasTranslations.php

resources/js/
├── components/
│   ├── ui/                    # shadcn — jangan diedit manual
│   ├── data-table/
│   │   ├── data-table.tsx
│   │   ├── data-table-toolbar.tsx
│   │   ├── data-table-pagination.tsx
│   │   ├── data-table-column-header.tsx
│   │   └── data-table-row-actions.tsx
│   ├── form/
│   │   ├── form-field-wrapper.tsx
│   │   ├── input-rupiah.tsx
│   │   ├── input-decimal.tsx
│   │   ├── date-picker.tsx
│   │   ├── date-range-picker.tsx
│   │   ├── combobox-select.tsx      # searchable, untuk opsi > 10
│   │   ├── translatable-tabs.tsx
│   │   └── rich-text-editor.tsx
│   ├── upload/
│   │   ├── image-uploader.tsx
│   │   ├── multi-image-uploader.tsx
│   │   ├── document-uploader.tsx
│   │   └── upload-progress.tsx
│   ├── shared/
│   │   ├── page-header.tsx          # breadcrumb + title + description
│   │   ├── breadcrumbs.tsx
│   │   ├── empty-state.tsx
│   │   ├── confirm-dialog.tsx
│   │   ├── status-badge.tsx
│   │   ├── translation-indicator.tsx
│   │   └── sortable-list.tsx
│   └── admin/
├── layouts/
│   ├── admin-layout.tsx
│   ├── auth-layout.tsx
│   └── public-layout.tsx
├── pages/
│   ├── admin/
│   │   ├── dashboard/index.tsx
│   │   ├── products/{index,create,edit}.tsx
│   │   └── ...
│   ├── auth/
│   └── public/
├── hooks/
├── lib/
│   ├── utils.ts
│   ├── format.ts               # rupiah, tanggal WIB, berat, CBM
│   └── locale.ts
└── types/
    ├── index.d.ts
    └── models.d.ts
```

---

## 4. Konvensi Penamaan

| Objek | Pola | Contoh |
|-------|------|--------|
| Tabel | snake_case jamak | `product_specifications` |
| Model | PascalCase tunggal | `ProductSpecification` |
| Controller | PascalCase + `Controller` | `ProductController` |
| Action | Kata kerja + objek + `Action` | `CreateProductAction` |
| Service | Kata benda + `Service` | `ContainerCalculatorService` |
| Repository | Model + `Repository` | `ProductRepository` |
| FormRequest | `{Store\|Update}` + Model + `Request` | `StoreProductRequest` |
| Enum | PascalCase tunggal | `RfqStatus` |
| Route name | titik, dari umum ke khusus | `admin.products.edit` |
| Komponen React | kebab-case file, PascalCase export | `data-table.tsx` → `DataTable` |
| Halaman Inertia | kebab-case folder + file | `admin/products/index` |
| Type TS | PascalCase | `Product`, `RfqRequest` |

---

## 5. Contoh Implementasi

### 5.1 Enum

```php
<?php

namespace App\Enums;

enum RfqStatus: string
{
    case New         = 'new';
    case Contacted   = 'contacted';
    case Quoted      = 'quoted';
    case Negotiating = 'negotiating';
    case Won         = 'won';
    case Lost        = 'lost';
    case Spam        = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New         => 'Baru',
            self::Contacted   => 'Dihubungi',
            self::Quoted      => 'Penawaran Dikirim',
            self::Negotiating => 'Negosiasi',
            self::Won         => 'Menang',
            self::Lost        => 'Kalah',
            self::Spam        => 'Spam',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New         => 'primary',
            self::Contacted   => 'info',
            self::Quoted      => 'warning',
            self::Negotiating => 'warning',
            self::Won         => 'success',
            self::Lost        => 'muted',
            self::Spam        => 'destructive',
        };
    }

    /** @return array<int, array{value: string, label: string}> */
    public static function options(): array
    {
        return array_map(
            fn (self $c) => ['value' => $c->value, 'label' => $c->label()],
            self::cases(),
        );
    }
}
```

### 5.2 Repository

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /** @param array<string, mixed> $filters */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Product;

    public function findBySlugOrFail(string $slug, string $locale): Product;

    /** @return Collection<int, Product> */
    public function calculable(): Collection;

    /** @param array<string, mixed> $data */
    public function create(array $data): Product;

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): bool;
}
```

```php
<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ProductRepository implements ProductRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category:id,name'])
            ->withCount(['specifications', 'documents'])
            ->when(
                $filters['search'] ?? null,
                fn ($q, string $s) => $q->where(function ($q) use ($s) {
                    $q->where('code', 'like', "%{$s}%")
                      ->orWhere('name->en', 'like', "%{$s}%");
                }),
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($q, int $id) => $q->where('product_category_id', $id),
            )
            ->when(
                isset($filters['is_active']),
                fn ($q) => $q->where('is_active', (bool) $filters['is_active']),
            )
            ->orderBy(
                $filters['sort_by'] ?? 'sort_order',
                $filters['sort_dir'] ?? 'asc',
            )
            ->paginate($perPage)
            ->withQueryString();
    }

    public function calculable(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->whereNotNull('master_carton_weight_kg')
            ->whereNotNull('master_carton_cbm')
            ->orderBy('sort_order')
            ->get();
    }

    // ... sisanya
}
```

Daftarkan binding di `AppServiceProvider`:

```php
$this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
```

### 5.3 Action

```php
<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\MediaUploadService;
use Illuminate\Support\Facades\DB;

final readonly class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private MediaUploadService $media,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->products->create(
                collect($data)->except(['specifications', 'cover', 'gallery', 'packaging_option_ids'])->all(),
            );

            if (! empty($data['specifications'])) {
                $product->specifications()->createMany($data['specifications']);
            }

            if (! empty($data['packaging_option_ids'])) {
                $product->packagingOptions()->sync($data['packaging_option_ids']);
            }

            if (! empty($data['cover'])) {
                $this->media->attachImage($product, $data['cover'], 'cover');
            }

            if (! empty($data['gallery'])) {
                $this->media->attachImages($product, $data['gallery'], 'gallery');
            }

            return $product->fresh(['specifications', 'packagingOptions']);
        });
    }
}
```

### 5.4 Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Product\CreateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

final class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'category_id', 'is_active', 'sort_by', 'sort_dir']);

        return inertia('admin/products/index', [
            'products'   => ProductResource::collection(
                $this->products->paginate($filters, (int) $request->integer('per_page', 15)),
            ),
            'categories' => ProductCategory::active()->get(['id', 'name']),
            'filters'    => $filters,
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        return to_route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }
}
```

### 5.5 Service Container Calculator

```php
<?php

namespace App\Services;

use App\Models\ContainerType;
use App\Models\Product;

final readonly class ContainerCalculatorService
{
    /** @return array{cartons:int, net_weight_kg:float, gross_weight_kg:float, volume_cbm:float, utilisation:float, limited_by:string} */
    public function calculate(Product $product, ContainerType $container): array
    {
        $cartonWeight = (float) $product->master_carton_weight_kg;
        $cartonCbm    = (float) $product->master_carton_cbm;
        $cartonTare   = (float) ($product->master_carton_tare_kg ?? 0);

        if ($cartonWeight <= 0 || $cartonCbm <= 0) {
            throw new \InvalidArgumentException(
                "Produk {$product->code} belum memiliki data master carton yang lengkap.",
            );
        }

        $byWeight = (int) floor((float) $container->max_payload_kg / $cartonWeight);
        $byVolume = (int) floor((float) $container->max_cbm / $cartonCbm);

        $cartons     = min($byWeight, $byVolume);
        $limitedBy   = $byWeight <= $byVolume ? 'weight' : 'volume';
        $netWeight   = $cartons * $cartonWeight;
        $volume      = $cartons * $cartonCbm;
        $utilisation = $limitedBy === 'weight'
            ? $netWeight / (float) $container->max_payload_kg
            : $volume / (float) $container->max_cbm;

        return [
            'cartons'         => $cartons,
            'net_weight_kg'   => round($netWeight, 2),
            'gross_weight_kg' => round($netWeight + ($cartons * $cartonTare), 2),
            'volume_cbm'      => round($volume, 3),
            'utilisation'     => round(min($utilisation, 1.0), 4),
            'limited_by'      => $limitedBy,
        ];
    }
}
```

Perhitungan ini **diduplikasi di sisi klien** (TypeScript) agar kalkulator responsif tanpa round-trip. Sisi server tetap ada sebagai sumber kebenaran untuk data yang dikirim ke halaman dan untuk pengujian.

### 5.6 FormRequest

```php
<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProductRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code'                    => ['required', 'string', 'max:50', Rule::unique('products', 'code')],
            'product_category_id'     => ['nullable', 'exists:product_categories,id'],
            'name.en'                 => ['required', 'string', 'max:255'],
            'name.ar'                 => ['nullable', 'string', 'max:255'],
            'name.ru'                 => ['nullable', 'string', 'max:255'],
            'slug.en'                 => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'short_description.en'    => ['nullable', 'string', 'max:500'],
            'description.en'          => ['nullable', 'string'],
            'master_carton_weight_kg' => ['nullable', 'numeric', 'min:0.001', 'max:9999.999'],
            'master_carton_cbm'       => ['nullable', 'numeric', 'min:0.0001', 'max:999.9999'],
            'master_carton_tare_kg'   => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'moq_kg'                  => ['nullable', 'numeric', 'min:0'],
            'is_active'               => ['boolean'],
            'is_featured'             => ['boolean'],
            'sort_order'              => ['integer', 'min:0'],
            'specifications'                => ['array'],
            'specifications.*.parameter.en' => ['required', 'string', 'max:255'],
            'specifications.*.value'        => ['required', 'string', 'max:255'],
            'specifications.*.unit'         => ['nullable', 'string', 'max:50'],
            'cover'                   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery'                 => ['array', 'max:20'],
            'gallery.*'               => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'code.required'                  => 'Kode produk wajib diisi.',
            'code.unique'                    => 'Kode produk ini sudah dipakai produk lain.',
            'name.en.required'               => 'Nama produk (Bahasa Inggris) wajib diisi.',
            'slug.en.required'               => 'Slug (Bahasa Inggris) wajib diisi.',
            'slug.en.regex'                  => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung.',
            'master_carton_weight_kg.numeric'=> 'Berat master carton harus berupa angka.',
            'master_carton_weight_kg.min'    => 'Berat master carton harus lebih dari 0.',
            'master_carton_cbm.numeric'      => 'Volume master carton harus berupa angka.',
            'specifications.*.parameter.en.required' => 'Nama parameter spesifikasi wajib diisi.',
            'specifications.*.value.required'=> 'Nilai spesifikasi wajib diisi.',
            'cover.image'                    => 'File sampul harus berupa gambar.',
            'cover.max'                      => 'Ukuran gambar sampul maksimal 5 MB.',
            'gallery.max'                    => 'Galeri maksimal 20 gambar sekaligus.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'master_carton_weight_kg' => 'berat master carton',
            'master_carton_cbm'       => 'volume master carton',
        ];
    }
}
```

---

## 6. Aturan Wajib

### PHP

1. `declare(strict_types=1);` di seluruh file PHP.
2. Kelas Action, Service, Repository dideklarasikan `final`. Action dan Service memakai `final readonly` bila hanya berisi dependensi konstruktor.
3. Semua parameter dan return type dideklarasikan eksplisit.
4. Array bertipe kompleks diberi anotasi PHPDoc generic untuk Larastan.
5. Dilarang `DB::raw()` dengan input pengguna.
6. Dilarang `->get()` tanpa batas pada tabel yang berpotensi besar.
7. Setiap operasi tulis yang menyentuh lebih dari satu tabel dibungkus `DB::transaction()`.
8. `updated_by` diisi lewat Observer, bukan manual di setiap Action.
9. Enum PHP untuk semua kolom bertipe ENUM di database. Jangan pakai string mentah.

### Frontend

1. TypeScript strict. Dilarang `any` — pakai `unknown` lalu persempit.
2. Tipe model dipusatkan di `types/models.d.ts` dan diselaraskan dengan API Resource.
3. Route dipanggil lewat helper Wayfinder, bukan string URL literal.
4. Dilarang `localStorage` dan `sessionStorage`.
5. Komponen shadcn di `components/ui/` tidak diedit manual; kustomisasi lewat `className` atau wrapper.
6. Form memakai `useForm` dari Inertia, bukan state manual.
7. Setiap `useEffect` yang memasang listener wajib punya cleanup.

### Query

1. Eager load eksplisit di repository. Aktifkan `Model::preventLazyLoading()` di environment non-produksi.
2. `select()` kolom yang dibutuhkan saja untuk relasi yang dipakai di dropdown.
3. Pagination server-side untuk seluruh DataTable. Dilarang mengirim seluruh dataset ke frontend.
4. `withCount()` untuk jumlah relasi, bukan menghitung di PHP.

---

## 7. Testing

Pest 4. Struktur:

```
tests/
├── Feature/
│   ├── Admin/
│   │   ├── ProductManagementTest.php
│   │   ├── RfqInboxTest.php
│   │   └── RepresentativeAuditTest.php
│   ├── Auth/
│   └── Public/
│       ├── ContainerCalculatorTest.php
│       └── RfqSubmissionTest.php
└── Unit/
    └── Services/ContainerCalculatorServiceTest.php
```

**Minimal yang wajib ada test:**

- `ContainerCalculatorService` — semua kombinasi produk × kontainer, termasuk kasus weight-limited, volume-limited, dan data tidak lengkap
- Submission RFQ — validasi, rate limit, honeypot, notifikasi
- Audit log pada perubahan `representatives`
- Otorisasi: pengguna tidak login tidak bisa mengakses rute admin
- Slug unik per locale
- Soft delete tidak menghapus data secara permanen

Perintah verifikasi sebelum commit:

```bash
./vendor/bin/pint
./vendor/bin/phpstan analyse
./vendor/bin/pest
npm run lint && npm run format:check && npx tsc --noEmit
```

---

## 8. Locale & Format Data

### Waktu

Database menyimpan UTC. Tampilan memakai WIB (`Asia/Jakarta`).

`config/app.php` → `'timezone' => 'UTC'`. Konversi dilakukan di Resource:

```php
'created_at' => $this->created_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
'created_at_iso' => $this->created_at?->toIso8601String(),
```

Kirim keduanya: yang terformat untuk tampilan, yang ISO untuk sorting di sisi klien.

### Angka

- **Rupiah** — disimpan `BIGINT` tanpa desimal. Tampilan memakai komponen `InputRupiah` dengan pemisah ribuan titik. Dipakai bila ada field harga; saat ini belum ada, tapi komponennya tetap dibuat karena kemungkinan besar diminta menyusul (harga indikatif, biaya sampel).
- **Berat & volume** — `DECIMAL`, tampilan memakai `InputDecimal` dengan pemisah desimal koma untuk admin Indonesia, tapi dikirim ke server dengan titik.
- Tampilan angka di tabel memakai `tabular-nums`.
