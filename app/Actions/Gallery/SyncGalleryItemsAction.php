<?php

declare(strict_types=1);

namespace App\Actions\Gallery;

use App\Enums\GalleryItemType;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Services\MediaUploadService;
use Illuminate\Support\Facades\DB;

/**
 * Menyimpan seluruh item album sekaligus: menambah item baru dari token
 * unggahan, memperbarui caption dan urutan, lalu menghapus item yang dibuang
 * admin beserta berkasnya.
 *
 * @see docs/09-tasks.md bagian 3.6
 */
final readonly class SyncGalleryItemsAction
{
    public function __construct(private MediaUploadService $media) {}

    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(Gallery $gallery, array $items): void
    {
        DB::transaction(function () use ($gallery, $items): void {
            /** @var array<int, GalleryItem> $existing */
            $existing = $gallery->items()->get()->keyBy('id')->all();
            $keptIds = [];

            foreach ($items as $position => $item) {
                $id = isset($item['id']) ? (int) $item['id'] : null;
                $type = GalleryItemType::from((string) ($item['type'] ?? 'image'));
                $token = is_string($item['token'] ?? null) ? $item['token'] : null;

                $attributes = [
                    'type' => $type,
                    'caption' => $item['caption'] ?? null,
                    'sort_order' => $position,
                    'video_url' => $type === GalleryItemType::Video
                        ? ($item['video_url'] ?? null)
                        : null,
                ];

                if ($token !== null) {
                    $file = $this->media->moveFromToken($token, GalleryItem::FILE_DIRECTORY);

                    if ($file !== null) {
                        $attributes['file_path'] = $file['path'];
                    }
                }

                if ($id !== null && isset($existing[$id])) {
                    $current = $existing[$id];
                    $previousPath = $current->file_path;

                    $current->update($attributes);
                    $keptIds[] = $id;

                    // Berkas lama baru dihapus setelah baris menunjuk berkas baru.
                    if ($previousPath !== null && $previousPath !== $current->file_path) {
                        $this->media->delete($previousPath);
                    }

                    continue;
                }

                $created = $gallery->items()->create($attributes);
                $keptIds[] = $created->id;
            }

            foreach ($existing as $id => $item) {
                if (in_array($id, $keptIds, true)) {
                    continue;
                }

                // Item yang dibuang admin dihapus permanen beserta berkasnya —
                // album tidak memakai soft delete di level item.
                $this->media->delete($item->file_path);
                $item->delete();
            }
        });
    }
}
