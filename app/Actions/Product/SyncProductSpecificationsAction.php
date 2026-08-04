<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

final readonly class SyncProductSpecificationsAction
{
    /**
     * Menyelaraskan spesifikasi produk dengan yang dikirim form.
     *
     * Baris ber-`id` diperbarui di tempat, baris tanpa `id` dibuat baru, dan
     * baris lama yang tidak ada di kiriman dihapus. Pendekatan ini dipilih
     * supaya menyimpan ulang form tidak menggandakan baris — kalau seluruh
     * spesifikasi dihapus lalu dibuat ulang, id-nya berubah setiap simpan dan
     * urutan bisa kacau.
     *
     * Datanya datang dari `validated()` yang bertipe longgar, jadi tiap kunci
     * dinormalkan di sini.
     *
     * @param  array<int, array<string, mixed>>  $specifications
     */
    public function handle(Product $product, array $specifications): void
    {
        DB::transaction(function () use ($product, $specifications): void {
            $keptIds = [];

            foreach (array_values($specifications) as $position => $specification) {
                $parameter = $specification['parameter'] ?? [];

                $attributes = [
                    'parameter' => is_array($parameter) ? $parameter : [],
                    'value' => (string) ($specification['value'] ?? ''),
                    'unit' => isset($specification['unit']) && $specification['unit'] !== ''
                        ? (string) $specification['unit']
                        : null,
                    'is_highlighted' => (bool) ($specification['is_highlighted'] ?? false),
                    'sort_order' => $position,
                ];

                $id = isset($specification['id']) ? (int) $specification['id'] : null;

                if ($id !== null && $id > 0) {
                    $existing = $product->specifications()->whereKey($id)->first();

                    if ($existing !== null) {
                        $existing->update($attributes);
                        $keptIds[] = $existing->id;

                        continue;
                    }
                }

                $created = $product->specifications()->create($attributes);
                $keptIds[] = $created->id;
            }

            $product->specifications()
                ->when($keptIds !== [], fn ($query) => $query->whereNotIn('id', $keptIds))
                ->delete();
        });
    }
}
