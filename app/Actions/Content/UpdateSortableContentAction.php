<?php

declare(strict_types=1);

namespace App\Actions\Content;

use App\Repositories\Contracts\SortableContentRepositoryInterface;
use App\Services\MediaUploadService;
use Illuminate\Database\Eloquent\Model;

/**
 * Pasangan ubah dari `CreateSortableContentAction`.
 *
 * @see docs/09-tasks.md bagian 3.3
 */
final readonly class UpdateSortableContentAction
{
    public function __construct(private MediaUploadService $media) {}

    /**
     * @template TModel of Model
     *
     * @param  SortableContentRepositoryInterface<TModel>  $repository
     * @param  TModel  $model
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $uploads  peta kolom berkas ke direktori tujuan
     * @return TModel
     */
    public function handle(
        SortableContentRepositoryInterface $repository,
        Model $model,
        array $data,
        array $uploads = [],
    ): Model {
        /** @var array<string, string|null> $previous */
        $previous = [];

        foreach ($uploads as $column => $directory) {
            $token = is_string($data["{$column}_token"] ?? null)
                ? $data["{$column}_token"]
                : null;
            $remove = (bool) ($data["remove_{$column}"] ?? false);

            unset($data["{$column}_token"], $data["remove_{$column}"]);

            $value = $model->getAttribute($column);
            $previous[$column] = is_string($value) ? $value : null;

            if ($token !== null) {
                $file = $this->media->moveFromToken($token, $directory);

                if ($file !== null) {
                    $data[$column] = $file['path'];
                }
            } elseif ($remove) {
                $data[$column] = null;
            }
        }

        $model = $repository->update($model, $data);

        // Berkas lama baru dihapus setelah baris menunjuk berkas baru.
        foreach ($previous as $column => $path) {
            $current = $model->getAttribute($column);

            if ($path !== null && $path !== $current) {
                $this->media->delete($path);
            }
        }

        return $model;
    }
}
