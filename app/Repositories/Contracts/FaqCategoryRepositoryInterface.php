<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\FaqCategory;

/**
 * @extends SortableContentRepositoryInterface<FaqCategory>
 */
interface FaqCategoryRepositoryInterface extends SortableContentRepositoryInterface
{
    /**
     * Jumlah FAQ yang masih memakai kategori ini.
     */
    public function countFaqs(int $faqCategoryId): int;

    /**
     * Melepas seluruh FAQ dari kategori ini dan mengembalikan jumlahnya.
     */
    public function detachFaqs(int $faqCategoryId): int;
}
