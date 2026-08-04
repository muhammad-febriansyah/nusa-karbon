<?php

declare(strict_types=1);

namespace App\Actions\Faq;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;

final readonly class UpdateFaqAction
{
    public function __construct(private FaqRepositoryInterface $faqs) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Faq $faq, array $data): Faq
    {
        $categoryId = isset($data['faq_category_id']) ? (int) $data['faq_category_id'] : null;

        // Pindah kategori menaruh FAQ di urutan terakhir kategori barunya,
        // supaya tidak menyalip pertanyaan yang sudah tersusun di sana.
        if ($categoryId !== $faq->faq_category_id) {
            $data['sort_order'] = $this->faqs->nextSortOrderInCategory($categoryId);
        }

        return $this->faqs->update($faq, $data);
    }
}
