<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ContainerType;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<ContainerType>
 */
interface ContainerTypeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Kontainer aktif berurutan, dipakai Container Calculator.
     *
     * @return Collection<int, ContainerType>
     */
    public function activeOrdered(): Collection;

    public function nextSortOrder(): int;

    /**
     * Kode wajib unik lintas kontainer.
     */
    public function codeExists(string $code, ?int $exceptId = null): bool;
}
