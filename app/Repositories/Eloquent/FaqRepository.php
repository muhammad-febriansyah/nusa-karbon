<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;
use App\Support\Locale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends SortableContentRepository<Faq>
 */
final class FaqRepository extends SortableContentRepository implements FaqRepositoryInterface
{
    /**
     * @var array<int, string>
     */
    protected array $searchable = [];

    public function __construct()
    {
        $locale = Locale::default();

        $this->searchable = ['question->'.$locale, 'answer->'.$locale];
    }

    /**
     * @return Builder<Faq>
     */
    protected function query(): Builder
    {
        return Faq::query();
    }

    /**
     * @return Collection<int, Faq>
     */
    public function ordered(): Collection
    {
        return $this->query()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Faq>
     */
    public function activeOrdered(): Collection
    {
        return $this->query()->active()->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, Faq>
     */
    public function orderedWithCategory(): Collection
    {
        return $this->query()
            ->with('category')
            ->orderBy('faq_category_id')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @return Collection<int, Faq>
     */
    public function activeWithCategory(): Collection
    {
        return $this->query()
            ->active()
            ->with('category')
            ->orderBy('faq_category_id')
            ->orderBy('sort_order')
            ->get();
    }

    public function nextSortOrderInCategory(?int $faqCategoryId): int
    {
        return (int) $this->query()
            ->where('faq_category_id', $faqCategoryId)
            ->max('sort_order') + 1;
    }
}
