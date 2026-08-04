<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\Product;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Produk acuan beranda beserta angka ujinya.
 *
 * Pembeli briket menyaring pemasok lewat empat angka — kadar karbon, abu, nilai
 * kalor, lama bakar — sebelum membalas email. Beranda memajang angka itu apa
 * adanya, jadi resource ini mengirim satu produk acuan lengkap dengan
 * spesifikasinya, bukan kalimat pemasaran tentangnya.
 *
 * @mixin Product
 */
final class SpecSheetResource extends JsonResource
{
    /**
     * Jumlah angka yang muat sebaris di layar lebar tanpa mengecilkan
     * ukurannya. Lebih dari ini, pita angkanya berubah jadi tabel dan kehilangan
     * fungsinya sebagai penyaring cepat.
     */
    private const SPEC_LIMIT = 4;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->text('name'),
            'slug' => $this->text('slug'),
            'shape' => $this->shape,
            'size_mm' => $this->size_mm,
            'specs' => $this->headlineSpecs(),
        ];
    }

    /**
     * Spesifikasi yang tampil di hero.
     *
     * Yang ditandai `is_highlighted` di admin selalu didahulukan; sisa kuotanya
     * diisi mengikuti urutan tampil biasa. Dengan begitu angka mana yang
     * dipajang adalah keputusan admin, bukan sesuatu yang tertanam di kode.
     *
     * @return list<array{parameter: string, value: string, unit: string|null}>
     */
    private function headlineSpecs(): array
    {
        $specs = $this->specifications
            ->sortByDesc(
                fn (ProductSpecification $spec): int => $spec->is_highlighted ? 1 : 0,
            )
            ->take(self::SPEC_LIMIT)
            ->map(fn (ProductSpecification $spec): array => [
                'parameter' => (string) $spec->getTranslation(
                    'parameter',
                    app()->getLocale(),
                ),
                'value' => $spec->value,
                'unit' => $spec->unit,
            ])
            ->all();

        /*
         * `sortByDesc()` mempertahankan kunci asli koleksi. Tanpa penataan
         * ulang ini, kunci yang meloncat membuat `json_encode` mengirimkannya
         * sebagai objek, bukan array — dan `specs.map()` di sisi React gagal.
         */
        return array_values($specs);
    }

    /**
     * Nilai kolom translatable untuk locale aktif. String kosong dianggap belum
     * diterjemahkan sehingga dikembalikan `null`.
     */
    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
