<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Representative;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepositoryInterface<Representative>
 */
interface RepresentativeRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Perwakilan aktif berurutan, dipakai halaman verifikasi publik.
     *
     * @return Collection<int, Representative>
     */
    public function activeOrdered(): Collection;

    /**
     * Perwakilan aktif yang cocok dengan nama, email, telepon, atau WhatsApp.
     *
     * Dipakai halaman verifikasi publik. Yang dicocokkan sengaja termasuk email
     * dan nomor meski keduanya tidak pernah dikirim balik ke pengunjung —
     * pembeli biasanya hanya memegang nomor WhatsApp orang yang menghubunginya,
     * bukan namanya.
     *
     * @return Collection<int, Representative>
     */
    public function searchActive(string $query): Collection;

    public function emailExists(string $email, ?int $exceptId = null): bool;

    public function nextSortOrder(): int;
}
