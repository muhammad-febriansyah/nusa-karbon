<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Repositories\Contracts\FaqCategoryRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<FaqCategory>
 */
final class FaqCategoryRepository extends SortableContentRepository implements FaqCategoryRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $this->searchable = ['name->'.Locale::default()];
    }

    /**
     * @return Builder<FaqCategory>
     */
    protected function query(): Builder
    {
        return FaqCategory::query();
    }

    /**
     * @return Collection<int, FaqCategory>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, FaqCategory>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    public function detachFaqs(int $faqCategoryId): int
    {
        return Faq::query()
            ->where('faq_category_id', $faqCategoryId)
            ->update(['faq_category_id' => null]);
    }

    public function countFaqs(int $faqCategoryId): int
    {
        return FaqCategory::query()
            ->whereKey($faqCategoryId)
            ->withCount('faqs')
            ->value('faqs_count') ?? 0;
    }
}
