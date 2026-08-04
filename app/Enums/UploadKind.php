<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Aturan validasi unggahan per jenis field.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.2
 */
enum UploadKind: string
{
    case ProductCover = 'product_cover';
    case Gallery = 'gallery';
    case Logo = 'logo';
    case Avatar = 'avatar';
    case Document = 'document';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::ProductCover => 'Sampul produk',
            self::Gallery => 'Galeri',
            self::Logo => 'Logo & ikon',
            self::Avatar => 'Foto profil',
            self::Document => 'Dokumen',
            self::Video => 'Video',
        };
    }

    /**
     * @return array<int, string>
     */
    public function extensions(): array
    {
        return match ($this) {
            self::ProductCover, self::Gallery => ['jpg', 'jpeg', 'png', 'webp'],
            self::Logo => ['png', 'webp', 'svg'],
            self::Avatar => ['jpg', 'jpeg', 'png', 'webp'],
            self::Document => ['pdf', 'xlsx'],
            self::Video => ['mp4', 'webm'],
        };
    }

    /**
     * Ukuran maksimal dalam kilobyte.
     */
    public function maxKilobytes(): int
    {
        return match ($this) {
            self::ProductCover, self::Gallery => 5 * 1024,
            self::Logo => 1024,
            self::Avatar => 2 * 1024,
            self::Document => 10 * 1024,
            self::Video => 50 * 1024,
        };
    }

    /**
     * Dimensi minimal gambar, null untuk jenis non-gambar.
     *
     * @return array{width: int, height: int}|null
     */
    public function minDimensions(): ?array
    {
        return match ($this) {
            self::ProductCover => ['width' => 1200, 'height' => 900],
            self::Gallery => ['width' => 800, 'height' => 600],
            self::Logo => ['width' => 200, 'height' => 200],
            self::Avatar => ['width' => 400, 'height' => 400],
            self::Document, self::Video => null,
        };
    }

    public function isImage(): bool
    {
        return $this->minDimensions() !== null;
    }

    /**
     * SVG hanya diterima untuk logo dan wajib disanitasi sebelum disimpan.
     */
    public function allowsSvg(): bool
    {
        return $this === self::Logo;
    }

    /**
     * @return array<int, string>
     */
    public function rules(): array
    {
        $rules = [
            'required',
            'file',
            'mimes:'.implode(',', $this->extensions()),
            'max:'.$this->maxKilobytes(),
        ];

        // SVG tidak punya dimensi raster, jadi aturan dimensi dilewati untuk
        // logo; pemeriksaannya dilakukan setelah sanitasi.
        $dimensions = $this->minDimensions();

        if ($dimensions !== null && ! $this->allowsSvg()) {
            $rules[] = 'image';
            $rules[] = "dimensions:min_width={$dimensions['width']},min_height={$dimensions['height']}";
        }

        return $rules;
    }
}
