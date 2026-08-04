<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

/**
 * Menerbitkan artikel sekarang juga, dipakai tombol terbitkan di admin dan
 * command penerbitan terjadwal.
 *
 * @see docs/09-tasks.md bagian 3.4
 */
final readonly class PublishPostAction
{
    public function __construct(private PostRepositoryInterface $posts) {}

    public function handle(Post $post): Post
    {
        return $this->posts->update($post, [
            'status' => PostStatus::Published->value,
            // Artikel terjadwal tetap memakai waktu tayang yang sudah diatur
            // supaya urutan di halaman publik sesuai rencana redaksi.
            'published_at' => $post->published_at ?? now(),
        ]);
    }
}
