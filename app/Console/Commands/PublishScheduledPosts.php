<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Post\PublishPostAction;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Console\Command;

/**
 * Menerbitkan artikel terjadwal yang waktu tayangnya sudah lewat.
 *
 * @see docs/09-tasks.md bagian 3.4
 */
final class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Menerbitkan artikel terjadwal yang waktu tayangnya sudah tiba';

    public function handle(
        PostRepositoryInterface $posts,
        PublishPostAction $publish,
    ): int {
        $due = $posts->dueForPublishing();

        foreach ($due as $post) {
            $publish->handle($post);
        }

        $this->info("{$due->count()} artikel terjadwal diterbitkan.");

        return self::SUCCESS;
    }
}
