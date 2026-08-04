<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

/**
 * @mixin Activity
 */
final class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $changes */
        $changes = $this->attribute_changes?->toArray() ?? [];

        return [
            'id' => $this->id,
            'log_name' => $this->log_name,
            'event' => $this->event,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_label' => $this->subject_type === null
                ? null
                : Str::headline(class_basename($this->subject_type)),
            'subject_id' => $this->subject_id,
            // Causer selalu `User` di aplikasi ini, tapi Spatie mengetiknya
            // sebagai Model generik; peristiwa tanpa pelaku berarti dari sistem.
            'causer_name' => $this->causer instanceof User
                ? $this->causer->name
                : 'Sistem',
            // Nilai lama vs baru; kosong pada peristiwa yang tidak mengubah kolom.
            'changes' => [
                'old' => $changes['old'] ?? [],
                'attributes' => $changes['attributes'] ?? [],
            ],
            'created_at' => $this->created_at?->format('d M Y, H:i').' WIB',
        ];
    }
}
