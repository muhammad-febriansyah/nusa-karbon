<?php

declare(strict_types=1);

namespace App\Actions\Faq;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;

final readonly class CreateFaqAction
{
    public function __construct(private FaqRepositoryInterface $faqs) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Faq
    {
        $categoryId = isset($data['faq_category_id']) ? (int) $data['faq_category_id'] : null;

        // Urutan berlaku di dalam kategori, jadi nomor terakhir dihitung per
        // kategori — bukan dari seluruh tabel.
        $data['sort_order'] = $this->faqs->nextSortOrderInCategory($categoryId);

        return $this->faqs->create($data);
    }
}
