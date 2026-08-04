<?php

declare(strict_types=1);

namespace App\Actions\Faq;

use App\Models\Faq;
use App\Repositories\Contracts\FaqRepositoryInterface;

final readonly class DeleteFaqAction
{
    public function __construct(private FaqRepositoryInterface $faqs) {}

    public function handle(Faq $faq): bool
    {
        return $this->faqs->delete($faq);
    }
}
