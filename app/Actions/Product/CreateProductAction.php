<?php

declare(strict_types=1);

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
        private SyncProductSpecificationsAction $syncSpecifications,
        private SyncProductDocumentsAction $syncDocuments,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            /** @var array<int, string> $coverTokens */
            $coverTokens = $data['cover_tokens'] ?? [];
            /** @var array<int, string> $galleryTokens */
            $galleryTokens = $data['gallery_tokens'] ?? [];
            /** @var array<int, array<string, mixed>> $specifications */
            $specifications = $data['specifications'] ?? [];
            /** @var array<int, array<string, mixed>> $documents */
            $documents = $data['documents'] ?? [];
            /** @var array<int, int> $packagingOptionIds */
            $packagingOptionIds = $data['packaging_option_ids'] ?? [];

            unset(
                $data['cover_tokens'],
                $data['gallery_tokens'],
                $data['specifications'],
                $data['documents'],
                $data['packaging_option_ids'],
            );

            $data['sort_order'] = $this->products->nextSortOrder();

            $product = $this->products->create($data);

            if ($specifications !== []) {
                $this->syncSpecifications->handle($product, $specifications);
            }

            if ($documents !== []) {
                $this->syncDocuments->handle($product, $documents);
            }

            $product->packagingOptions()->sync($packagingOptionIds);

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
