<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ContactMessage;

/**
 * @extends BaseRepositoryInterface<ContactMessage>
 */
interface ContactMessageRepositoryInterface extends BaseRepositoryInterface
{
    public function countUnread(): int;
}
