<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\MediaUploadService;
use App\Support\PostContent;
use Illuminate\Support\Facades\DB;

final readonly class UpdatePostAction
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private MediaUploadService $media,
        private SyncPostTagsAction $syncTags,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            /** @var list<string> $tags */
            $tags = $data['tags'] ?? [];
            $token = is_string($data['cover_token'] ?? null) ? $data['cover_token'] : null;
            $removeCover = (bool) ($data['remove_cover'] ?? false);

            unset($data['tags'], $data['cover_token'], $data['remove_cover']);

            $previousPath = $post->cover_path;

            if ($token !== null) {
                $cover = $this->media->moveFromToken($token, Post::COVER_DIRECTORY);

                if ($cover !== null) {
                    $data['cover_path'] = $cover['path'];
                }
            } elseif ($removeCover) {
                $data['cover_path'] = null;
            }

            $data['content'] = PostContent::sanitiseTranslations($data['content'] ?? null);
            $data['reading_minutes'] = PostContent::readingMinutes($data['content']);

            $status = $data['status'] ?? $post->status->value;

            if ($status === PostStatus::Published->value && empty($data['published_at'])) {
                $data['published_at'] = $post->published_at ?? now();
            }

            if ($status === PostStatus::Scheduled->value && empty($data['published_at'])) {
                $data['status'] = PostStatus::Draft->value;
            }

            $post = $this->posts->update($post, $data);

            $this->syncTags->handle($post, $tags);

            // Sampul lama baru dihapus setelah baris menunjuk berkas baru.
            if ($previousPath !== null && $previousPath !== $post->cover_path) {
                $this->media->delete($previousPath);
            }

            return $post->load('tags');
        });
    }
}
