<?php

declare(strict_types=1);

namespace App\Support\Demo;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penulis PDF satu halaman untuk berkas contoh (COA, MSDS, Certificate of
 * Origin, dan sejenisnya).
 *
 * Dokumen dibuat sendiri, bukan diunduh: berkas PDF milik pihak lain tidak
 * boleh ikut masuk repo, dan isinya harus menyebut produk yang benar supaya
 * demo terlihat masuk akal saat dibuka. Hasilnya PDF 1.4 yang sah dan bisa
 * dibuka pembaca mana pun.
 */
final class DemoPdf
{
    private const PAGE_WIDTH = 595;  // A4 pada 72 dpi

    private const PAGE_HEIGHT = 842;

    /**
     * @param  array<int, array{0: string, 1: string}>  $rows  pasangan label dan nilai
     */
    public function write(
        string $path,
        string $title,
        string $subtitle,
        array $rows,
        string $footer = 'Dokumen contoh untuk keperluan demonstrasi.',
    ): int {
        $content = $this->contentStream($title, $subtitle, $rows, $footer);
        $pdf = $this->document($content);

        Storage::disk('public')->put($path, $pdf);

        return strlen($pdf);
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $rows
     */
    private function contentStream(string $title, string $subtitle, array $rows, string $footer): string
    {
        $lines = [
            'BT',
            '/F2 20 Tf',
            '1 0 0 1 56 762 Tm',
            '('.$this->escape('PT NUSA CARBON INDONESIA').') Tj',
            'ET',
            'BT',
            '/F1 10 Tf',
            '1 0 0 1 56 744 Tm',
            '('.$this->escape('Coconut shell charcoal briquette manufacturer & exporter').') Tj',
            'ET',
            // Garis pemisah kop surat.
            '0.85 w',
            '56 732 m',
            (self::PAGE_WIDTH - 56).' 732 l',
            'S',
            'BT',
            '/F2 16 Tf',
            '1 0 0 1 56 700 Tm',
            '('.$this->escape($title).') Tj',
            'ET',
            'BT',
            '/F1 11 Tf',
            '1 0 0 1 56 682 Tm',
            '('.$this->escape($subtitle).') Tj',
            'ET',
        ];

        $y = 646;

        foreach ($rows as [$label, $value]) {
            $lines[] = 'BT';
            $lines[] = '/F2 10 Tf';
            $lines[] = '1 0 0 1 56 '.$y.' Tm';
            $lines[] = '('.$this->escape($label).') Tj';
            $lines[] = 'ET';
            $lines[] = 'BT';
            $lines[] = '/F1 10 Tf';
            $lines[] = '1 0 0 1 240 '.$y.' Tm';
            $lines[] = '('.$this->escape($value).') Tj';
            $lines[] = 'ET';

            $y -= 22;
        }

        $lines[] = 'BT';
        $lines[] = '/F1 9 Tf';
        $lines[] = '1 0 0 1 56 96 Tm';
        $lines[] = '('.$this->escape($footer).') Tj';
        $lines[] = 'ET';

        return implode("\n", $lines);
    }

    private function document(string $content): string
    {
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ".self::PAGE_WIDTH.' '.self::PAGE_HEIGHT.']'
                ." /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>\nendobj\n",
            "4 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>\nendobj\n",
            "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $startXref = strlen($pdf);
        $count = count($objects) + 1;

        $pdf .= "xref\n0 {$count}\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$startXref}\n%%EOF";

        return $pdf;
    }

    /**
     * PDF memakai WinAnsi, jadi karakter di luar Latin-1 diganti dan tanda
     * kurung di-escape supaya stream-nya tidak rusak.
     */
    private function escape(string $value): string
    {
        $ascii = Str::ascii($value);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}
