<?php

declare(strict_types=1);

namespace App\Support;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * SVG dapat memuat skrip, jadi berkas logo wajib melewati kelas ini sebelum
 * disimpan. Pendekatannya daftar putih: hanya elemen dan atribut yang dikenal
 * yang dipertahankan.
 *
 * @see docs/07-admin-ui-standards.md bagian 6.2
 */
final class SvgSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_ELEMENTS = [
        'svg', 'g', 'defs', 'title', 'desc', 'symbol', 'use',
        'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
        'text', 'tspan',
        'linearGradient', 'radialGradient', 'stop',
        'clipPath', 'mask', 'pattern',
    ];

    /**
     * @var array<int, string>
     */
    private const ALLOWED_ATTRIBUTES = [
        'id', 'class', 'viewBox', 'xmlns', 'xmlns:xlink', 'version',
        'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2', 'cx', 'cy', 'r',
        'rx', 'ry', 'd', 'points', 'transform', 'preserveAspectRatio',
        'fill', 'fill-opacity', 'fill-rule', 'stroke', 'stroke-width',
        'stroke-linecap', 'stroke-linejoin', 'stroke-dasharray',
        'stroke-opacity', 'opacity', 'offset', 'stop-color', 'stop-opacity',
        'gradientUnits', 'gradientTransform', 'clip-path', 'mask',
        'font-family', 'font-size', 'font-weight', 'text-anchor',
    ];

    /**
     * Mengembalikan SVG bersih, atau null bila berkasnya bukan SVG yang valid.
     */
    public function sanitize(string $svg): ?string
    {
        if (trim($svg) === '') {
            return null;
        }

        $document = new DOMDocument;
        $document->preserveWhiteSpace = false;

        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOENT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded === false) {
            return null;
        }

        // Doctype eksternal dapat memuat entitas jarak jauh.
        if ($document->doctype !== null) {
            $document->removeChild($document->doctype);
        }

        $root = $document->documentElement;

        if ($root === null || strtolower($root->nodeName) !== 'svg') {
            return null;
        }

        $this->cleanNode($root);

        $result = $document->saveXML($root);

        return $result === false ? null : $result;
    }

    private function cleanNode(DOMNode $node): void
    {
        // Ditelusuri terbalik karena daftar anak berubah saat elemen dihapus.
        for ($index = $node->childNodes->length - 1; $index >= 0; $index--) {
            $child = $node->childNodes->item($index);

            if (! $child instanceof DOMNode) {
                continue;
            }

            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }

            if ($child instanceof DOMElement) {
                if (! in_array($child->nodeName, self::ALLOWED_ELEMENTS, true)) {
                    $node->removeChild($child);

                    continue;
                }

                $this->cleanAttributes($child);
                $this->cleanNode($child);
            }
        }

        if ($node instanceof DOMElement) {
            $this->cleanAttributes($node);
        }
    }

    private function cleanAttributes(DOMElement $element): void
    {
        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);

            if (! $attribute instanceof DOMAttr) {
                continue;
            }

            $name = $attribute->nodeName;

            if (! in_array($name, self::ALLOWED_ATTRIBUTES, true)) {
                $element->removeAttribute($name);

                continue;
            }

            // Menutup `href="javascript:"` dan sejenisnya pada atribut nilai.
            if (preg_match('/(javascript|data|vbscript)\s*:/i', $attribute->nodeValue ?? '') === 1) {
                $element->removeAttribute($name);
            }
        }
    }
}
