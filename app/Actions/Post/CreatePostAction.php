<?php

declare(strict_types=1);

namespace App\Actions\Post;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\MediaUploadService;
use App\Support\PostContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final readonly class CreatePostAction
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private MediaUploadService $media,
        private SyncPostTagsAction $syncTags,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Post
    {
        return DB::transaction(function () use ($data): Post {
            /** @var list<string> $tags */
            $tags = $data['tags'] ?? [];
            $token = is_string($data['cover_token'] ?? null) ? $data['cover_token'] : null;

            unset($data['tags'], $data['cover_token'], $data['remove_cover']);

            if ($token !== null) {
                $cover = $this->media->moveFromToken($token, Post::COVER_DIRECTORY);
                $data['cover_path'] = $cover['path'] ?? null;
            }

            $data['content'] = PostContent::sanitiseTranslations($data['content'] ?? null);
            $data['reading_minutes'] = PostContent::readingMinutes($data['content']);
            $data['user_id'] = Auth::id();
            $data = $this->normaliseSchedule($data);

            $post = $this->posts->create($data);

            $this->syncTags->handle($post, $tags);

            return $post->load('tags');
        });
    }

    /**
     * Artikel terbit tanpa waktu tayang dianggap tayang sekarang; artikel
     * terjadwal wajib punya waktu tayang, kalau kosong dikembalikan ke draf.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normaliseSchedule(array $data): array
    {
        $status = $data['status'] ?? PostStatus::Draft->value;

        if ($status === PostStatus::Published->value && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($status === PostStatus::Scheduled->value && empty($data['published_at'])) {
            $data['status'] = PostStatus::Draft->value;
        }

        return $data;
    }
}
