<?php

declare(strict_types=1);

namespace App\Http\Resources\Public;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Anggota tim di halaman Tentang Kami.
 *
 * `linkedin_url` ikut karena pembeli lintas negara memakainya untuk memastikan
 * orang yang menghubungi mereka benar-benar bekerja di perusahaan ini — bagian
 * dari pencegahan penipuan yang sama dengan halaman verifikasi perwakilan.
 *
 * @mixin TeamMember
 */
final class TeamMemberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->text('position'),
            'bio' => $this->text('bio'),
            'photo_url' => $this->photoUrl(),
            'linkedin_url' => $this->linkedin_url,
        ];
    }

    private function text(string $key): ?string
    {
        $value = trim((string) $this->getTranslation($key, app()->getLocale()));

        return $value === '' ? null : $value;
    }
}
