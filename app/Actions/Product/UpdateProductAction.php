<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\MediaUploadService;
use Illuminate\Support\Facades\DB;

final readonly class UpdateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $products,
        private MediaUploadService $media,
        private SyncProductSpecificationsAction $syncSpecifications,
        private SyncProductDocumentsAction $syncDocuments,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            /** @var array<int, string> $coverTokens */
            $coverTokens = $data['cover_tokens'] ?? [];
            /** @var array<int, string> $galleryTokens */
            $galleryTokens = $data['gallery_tokens'] ?? [];
            /** @var array<int, int> $removedMedia */
            $removedMedia = $data['removed_media_ids'] ?? [];
            $hasSpecifications = array_key_exists('specifications', $data);
            /** @var array<int, array<string, mixed>> $specifications */
            $specifications = $data['specifications'] ?? [];
            $hasDocuments = array_key_exists('documents', $data);
            /** @var array<int, array<string, mixed>> $documents */
            $documents = $data['documents'] ?? [];
            $hasPackagingOptions = array_key_exists('packaging_option_ids', $data);
            /** @var array<int, int> $packagingOptionIds */
            $packagingOptionIds = $data['packaging_option_ids'] ?? [];

            unset(
                $data['cover_tokens'],
                $data['gallery_tokens'],
                $data['removed_media_ids'],
                $data['specifications'],
                $data['documents'],
                $data['packaging_option_ids'],
            );

            $product = $this->products->update($product, $data);

            // Hanya disinkronkan bila form memang mengirim spesifikasi, supaya
            // pembaruan parsial tidak menghapus baris yang sudah ada.
            if ($hasSpecifications) {
                $this->syncSpecifications->handle($product, $specifications);
            }

            // Alasan yang sama seperti spesifikasi: pembaruan parsial tidak
            // boleh menghapus dokumen yang sudah ada.
            if ($hasDocuments) {
                $this->syncDocuments->handle($product, $documents);
            }

            if ($hasPackagingOptions) {
                $product->packagingOptions()->sync($packagingOptionIds);
            }

            if ($removedMedia !== []) {
                $product->media()->whereIn('id', $removedMedia)->each(
                    fn ($media) => $media->delete(),
                );
            }

            // Sampul bersifat file tunggal: unggahan baru menggantikan yang lama.
            $this->media->attachFromTokens(
                $product,
                $coverTokens,
                Product::COLLECTION_COVER,
                replaceExisting: true,
            );

            $this->media->attachFromTokens(
                $product,
                $galleryTokens,
                Product::COLLECTION_GALLERY,
            );

            return $product->fresh([
                'category', 'media', 'specifications', 'documents', 'packagingOptions',
            ]) ?? $product;
        });
    }
}
