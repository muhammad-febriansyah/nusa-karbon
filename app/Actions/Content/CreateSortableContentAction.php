<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Repositories\Contracts\SortableContentRepositoryInterface;
use App\Services\MediaUploadService;
use Illuminate\Database\Eloquent\Model;

/**
 * Action bersama untuk keenam modul konten perusahaan. Perilakunya identik —
 * pindahkan berkas unggahan sementara, taruh di urutan terakhir, simpan — jadi
 * repository dan nama kolom berkasnya dioper sebagai argumen alih-alih
 * menggandakan kelas yang sama enam kali.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final readonly class CreateSortableContentAction
{
    public function __construct(private MediaUploadService $media) {}

    /**
     * @template TModel of Model
     *
     * @param  SortableContentRepositoryInterface<TModel>  $repository
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $uploads  peta kolom berkas ke direktori tujuan
     * @return TModel
     */
    public function handle(
        SortableContentRepositoryInterface $repository,
        array $data,
        array $uploads = [],
    ): Model {
        foreach ($uploads as $column => $directory) {
            $token = is_string($data["{$column}_token"] ?? null)
                ? $data["{$column}_token"]
                : null;

            unset($data["{$column}_token"], $data["remove_{$column}"]);

            if ($token !== null) {
                $file = $this->media->moveFromToken($token, $directory);
                $data[$column] = $file['path'] ?? null;
            }
        }

        $data['sort_order'] = $repository->nextSortOrder();

        return $repository->create($data);
    }
}
