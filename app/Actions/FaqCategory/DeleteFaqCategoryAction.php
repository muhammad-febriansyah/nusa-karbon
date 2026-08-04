<?php

declare(strict_types=1);

namespace App\Actions\FaqCategory;

use App\Models\FaqCategory;
use App\Repositories\Contracts\FaqCategoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteFaqCategoryAction
{
    public function __construct(
        private FaqCategoryRepositoryInterface $categories,
    ) {}

    /**
     * Kategori memakai soft delete, jadi foreign key `ON DELETE SET NULL`
     * tidak ikut jalan. Pertanyaannya dilepas lebih dulu supaya tidak
     * menggantung pada kategori yang sudah diarsipkan.
     *
     * @return int jumlah pertanyaan yang dilepas
     */
    public function handle(FaqCategory $category): int
    {
        return DB::transaction(function () use ($category): int {
            $detached = $this->categories->detachFaqs($category->id);

            $this->categories->delete($category);

            return $detached;
        });
    }
}
